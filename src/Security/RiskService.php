<?php
namespace Oxalis\Security;

use Carbon\Carbon;
use Oxalis\Models\AuthEvent;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Scores each login attempt 0–100.
 * Signals: new device fingerprint, impossible travel, first login, VPN/proxy.
 * All external lookups are cached and fail-open (never block the login).
 */
class RiskService
{
    public function score(
        Authenticatable $user,
        string $ip,
        string $userAgent,
    ): int {
        if (!config('oxalis.risk.enabled', false)) {
            return 0;
        }

        $total  = 0;
        $userId = $user->getAuthIdentifier();

        // ── New device fingerprint (+30) ────────────────────────────────────
        $fingerprint  = $this->deviceFingerprint($ip, $userAgent);
        $knownDevices = AuthEvent::where('user_id', $userId)
            ->whereNotNull('device_fingerprint')
            ->where('status', 'success')
            ->pluck('device_fingerprint')
            ->unique()
            ->all();

        if (!in_array($fingerprint, $knownDevices, true)) {
            $total += empty($knownDevices) ? 10 : 30; // first ever = low noise
        }

        // ── Impossible travel (+10 … +40) ───────────────────────────────────
        if (config('oxalis.risk.geo', true)) {
            $last = AuthEvent::where('user_id', $userId)
                ->where('status', 'success')
                ->whereNotNull('ip_address')
                ->latest()
                ->first();

            if ($last && $last->ip_address && $last->ip_address !== $ip) {
                $total += $this->impossibleTravelScore($last->ip_address, $ip, $last->created_at);
            }
        }

        // ── VPN / proxy detection (+10 … +20, opt-in) ─────────────────────
        if (config('oxalis.risk.vpn', false)) {
            $total += $this->vpnScore($ip);
        }

        return min($total, 100);
    }

    public function deviceFingerprint(string $ip, string $userAgent): string
    {
        // Use first 3 IPv4 octets so subnet changes don't create false positives
        $parts = explode('.', $ip);
        $prefix = count($parts) === 4
            ? "{$parts[0]}.{$parts[1]}.{$parts[2]}"
            : $ip;

        return hash('sha256', $prefix . '|' . $userAgent);
    }

    private function impossibleTravelScore(string $prevIp, string $newIp, Carbon $prevTime): int
    {
        $prev = Cache::remember("ox_geo_{$prevIp}", 3600, fn() => $this->geoLookup($prevIp));
        $curr = Cache::remember("ox_geo_{$newIp}",  3600, fn() => $this->geoLookup($newIp));

        if (!$prev || !$curr) {
            return 0;
        }

        $distKm   = $this->haversineKm($prev['lat'], $prev['lon'], $curr['lat'], $curr['lon']);
        $minutes  = max(1, $prevTime->diffInMinutes(now()));
        $speedKmh = ($distKm / $minutes) * 60;

        if ($speedKmh > 1200) {
            return 40; // physically impossible
        }
        if ($speedKmh > 900) {
            return 25; // faster than any commercial flight
        }
        if ($speedKmh > 400) {
            return 10; // cross-continental in suspicious time
        }

        return 0;
    }

    private function geoLookup(string $ip): ?array
    {
        try {
            // ip-api.com free tier — 45 req/min, no key required
            $data = Http::timeout(2)
                ->get("http://ip-api.com/json/{$ip}?fields=lat,lon,countryCode,status")
                ->json();

            if (($data['status'] ?? '') !== 'success') {
                return null;
            }

            return [
                'lat' => (float) ($data['lat'] ?? 0),
                'lon' => (float) ($data['lon'] ?? 0),
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R    = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $R * 2 * asin(sqrt($a));
    }

    private function vpnScore(string $ip): int
    {
        try {
            $data = Cache::remember("ox_vpn_{$ip}", 3600, function () use ($ip) {
                return Http::timeout(2)
                    ->get("http://ip-api.com/json/{$ip}?fields=proxy,hosting,status")
                    ->json();
            });

            if ($data['proxy'] ?? false) {
                return 20;
            }
            if ($data['hosting'] ?? false) {
                return 10;
            }
        } catch (\Throwable) {}

        return 0;
    }
}
