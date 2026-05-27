<?php
namespace Oxalis\Auth;

use Oxalis\Events\UserLoggedIn;
use Oxalis\Mail\LoginContextMail;
use Oxalis\Mail\LoginNotificationMail;
use Oxalis\Models\AuthEvent;
use Oxalis\Models\OxalisSession;
use Oxalis\Models\TotpSecret;
use Oxalis\Models\TotpTrustedDevice;
use Oxalis\Security\RiskService;
use Oxalis\Webhooks\WebhookService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

/**
 * Single entry point for every login path.
 * If the user has TOTP enabled, redirects to the TOTP challenge instead of
 * immediately creating a session — ensuring 2FA is always enforced.
 */
class LoginHandler
{
    public function attempt(
        Authenticatable $user,
        string          $method,
        string          $ip,
        string          $userAgent,
        bool            $remember = false,
    ): RedirectResponse {
        // If the user has TOTP enabled, hold the session in pending state
        $hasTotpEnabled = TotpSecret::where('user_id', $user->getAuthIdentifier())
            ->whereNotNull('confirmed_at')
            ->exists();

        if ($hasTotpEnabled) {
            // Check if this device was trusted after a previous TOTP verification
            if (config('oxalis.totp_trust.enabled', true)) {
                $trustToken = request()->cookie('oxalis_totp_trust');
                if ($trustToken) {
                    try {
                        $trusted = TotpTrustedDevice::where('user_id', $user->getAuthIdentifier())
                            ->where('token', hash('sha256', $trustToken))
                            ->where('expires_at', '>', now())
                            ->first();
                        if ($trusted) {
                            goto login;
                        }
                    } catch (\Throwable) {}
                }
            }

            session([
                'oxalis_totp_pending_user_id'   => $user->getAuthIdentifier(),
                'oxalis_totp_pending_method'     => $method,
                'oxalis_totp_pending_remember'   => $remember,
                'oxalis_totp_pending_ip'         => $ip,
                'oxalis_totp_pending_user_agent' => $userAgent,
            ]);

            return redirect()->route('oxalis.totp.verify.show');
        }

        login:

        Auth::login($user, $remember);
        request()->session()->regenerate();

        $this->recordLogin($user, $method, $ip, $userAgent);

        return redirect()->intended(config('oxalis.routes.home', '/dashboard'));
    }

    /**
     * Complete login after TOTP verification.
     */
    public function loginAfterTotp(
        Authenticatable $user,
        string          $method,
        string          $ip,
        string          $userAgent,
        bool            $remember = false,
    ): RedirectResponse {
        Auth::login($user, $remember);
        request()->session()->regenerate();

        $fullMethod = $method ? "{$method}+totp" : 'totp';

        $this->recordLogin($user, $fullMethod, $ip, $userAgent);

        return redirect()->intended(config('oxalis.routes.home', '/dashboard'));
    }

    private function recordLogin(
        Authenticatable $user,
        string $method,
        string $ip,
        string $userAgent,
    ): void {
        $userId = $user->getAuthIdentifier();

        // ── IP privacy ──────────────────────────────────────────────────────
        $storedIp = match (true) {
            !config('oxalis.store_ip', true)      => null,
            config('oxalis.ip_anonymize', false)  => $this->anonymizeIp($ip),
            default                               => $ip,
        };

        // ── Risk engine ─────────────────────────────────────────────────────
        $riskScore   = 0;
        $fingerprint = null;
        if (config('oxalis.risk.enabled', false)) {
            try {
                $risk        = app(RiskService::class);
                $riskScore   = $risk->score($user, $ip, $userAgent);
                $fingerprint = $risk->deviceFingerprint($ip, $userAgent);
            } catch (\Throwable) {}
        }

        // ── Auth event record ───────────────────────────────────────────────
        try {
            AuthEvent::create([
                'user_id'            => $userId,
                'event'              => 'login',
                'method'             => $method,
                'ip_address'         => $storedIp,
                'user_agent'         => $userAgent,
                'status'             => 'success',
                'risk_score'         => $riskScore,
                'device_fingerprint' => $fingerprint,
            ]);
        } catch (\Throwable) {}

        // ── Concurrent session limit ────────────────────────────────────────
        $maxSessions = config('oxalis.max_sessions', 0);
        if ($maxSessions > 0) {
            try {
                $count = OxalisSession::where('user_id', $userId)->count();
                if ($count >= $maxSessions) {
                    OxalisSession::where('user_id', $userId)
                        ->orderBy('last_active_at')
                        ->limit($count - $maxSessions + 1)
                        ->get()
                        ->each->delete();
                }
            } catch (\Throwable) {}
        }

        // ── Active session record ───────────────────────────────────────────
        try {
            $token = OxalisSession::createForUser(
                userId:    $userId,
                ip:        $storedIp ?? $ip,
                userAgent: $userAgent,
                method:    $method,
            );
            session(['oxalis_session_token' => $token]);
        } catch (\Throwable) {}

        event(new UserLoggedIn($user, $method, $ip, $userAgent));

        // ── Webhook ─────────────────────────────────────────────────────────
        try {
            app(WebhookService::class)->fire('login', [
                'user_id' => hash('sha256', (string) $userId),
                'method'  => $method,
                'ip'      => $storedIp,
            ]);
        } catch (\Throwable) {}

        // ── Login notification email (existing feature) ─────────────────────
        if (config('oxalis.login_notification', false)
            && !in_array(config('mail.default'), ['log', 'array', 'null'])) {
            try {
                Mail::to($user->email)->send(
                    new LoginNotificationMail($method, $ip, $userAgent, now()->toDateTimeString())
                );
            } catch (\Throwable) {}
        }

        // ── Login context email (new device / high risk) ────────────────────
        $threshold = config('oxalis.risk.threshold', 40);
        if (config('oxalis.login_context_email', false)
            && $riskScore >= $threshold
            && !in_array(config('mail.default'), ['log', 'array', 'null'])) {
            try {
                Mail::to($user->email)->send(new LoginContextMail(
                    method:    $method,
                    ip:        $ip,
                    userAgent: $userAgent,
                    riskScore: $riskScore,
                    timestamp: now()->toDateTimeString(),
                ));
            } catch (\Throwable) {}
        }
    }

    private function anonymizeIp(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts    = explode('.', $ip);
            $parts[3] = '0';
            return implode('.', $parts);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Truncate to /64 (first 4 groups)
            $groups = explode(':', $ip);
            return implode(':', array_slice($groups, 0, 4)) . '::';
        }

        return $ip;
    }
}
