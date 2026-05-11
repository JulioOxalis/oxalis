<?php
namespace Oxalis\Auth;

use Oxalis\Events\UserLoggedIn;
use Oxalis\Mail\LoginNotificationMail;
use Oxalis\Models\AuthEvent;
use Oxalis\Models\TotpSecret;
use Oxalis\Models\TotpTrustedDevice;
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
                            // Device is trusted — skip TOTP and log in directly
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

        AuthEvent::create([
            'user_id'    => $user->getAuthIdentifier(),
            'event'      => 'login',
            'method'     => $method,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'status'     => 'success',
        ]);

        event(new UserLoggedIn($user, $method, $ip, $userAgent));

        if (config('oxalis.login_notification', false) && !in_array(config('mail.default'), ['log', 'array', 'null'])) {
            Mail::to($user->email)->send(new LoginNotificationMail($method, $ip, $userAgent, now()->toDateTimeString()));
        }

        return redirect(config('oxalis.routes.home', '/dashboard'));
    }

    /**
     * Complete login after TOTP verification.
     * Skips the TOTP check (already done), but still logs the event and fires notifications.
     */
    public function loginAfterTotp(
        Authenticatable $user,
        string          $method,
        string          $ip,
        string          $userAgent,
        bool            $remember = false,
    ): RedirectResponse {
        Auth::login($user, $remember);

        $fullMethod = $method ? "{$method}+totp" : 'totp';

        AuthEvent::create([
            'user_id'    => $user->getAuthIdentifier(),
            'event'      => 'login',
            'method'     => $fullMethod,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'status'     => 'success',
        ]);

        event(new UserLoggedIn($user, $fullMethod, $ip, $userAgent));

        if (config('oxalis.login_notification', false) && !in_array(config('mail.default'), ['log', 'array', 'null'])) {
            Mail::to($user->email)->send(new LoginNotificationMail($fullMethod, $ip, $userAgent, now()->toDateTimeString()));
        }

        return redirect(config('oxalis.routes.home', '/dashboard'));
    }
}
