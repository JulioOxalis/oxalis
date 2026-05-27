<?php
namespace Oxalis\Ultrasonic;

use Illuminate\Support\Facades\Cache;

/**
 * Server-side token management for ultrasonic proximity authentication.
 *
 * Flow:
 *   1. Desktop calls begin()  → server issues an 8-char hex token
 *   2. Desktop plays token as 4-FSK ultrasonic audio (~1 second)
 *   3. Mobile (authenticated, mic enabled) hears and decodes the token
 *   4. Mobile calls approve() with the decoded token
 *   5. Desktop calls poll()   → receives 'approved' + redirect URL
 */
class UltrasonicService
{
    private const PREFIX = 'ox_ult_';

    public function generateToken(): array
    {
        // 8 hex chars = 32-bit token space (4 billion possibilities per TTL window)
        $token = strtoupper(bin2hex(random_bytes(4)));
        $ttl   = config('oxalis.ultrasonic.ttl', 30);

        Cache::put(self::PREFIX . $token, ['status' => 'pending', 'user_id' => null], $ttl);

        return ['token' => $token, 'ttl' => $ttl];
    }

    public function check(string $token): array
    {
        return Cache::get(self::PREFIX . strtoupper($token)) ?? ['status' => 'expired'];
    }

    public function approve(string $token, int|string $userId): bool
    {
        $token = strtoupper($token);
        $key   = self::PREFIX . $token;
        $data  = Cache::get($key);

        if (!$data || $data['status'] !== 'pending') {
            return false;
        }

        Cache::put($key, ['status' => 'approved', 'user_id' => $userId],
            config('oxalis.ultrasonic.ttl', 30));

        return true;
    }

    /** Returns the user_id and invalidates the token (single-use). */
    public function consume(string $token): int|string|null
    {
        $token = strtoupper($token);
        $key   = self::PREFIX . $token;
        $data  = Cache::get($key);

        if (!$data || $data['status'] !== 'approved') {
            return null;
        }

        Cache::forget($key);
        return $data['user_id'];
    }
}
