<?php
namespace Oxalis\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureMethodEnabled
{
    public function handle(Request $request, Closure $next, string $method)
    {
        abort_unless($this->enabled($method), 404);

        return $next($request);
    }

    private function enabled(string $method): bool
    {
        $method = strtolower($method);
        $passkeyOnly = (bool) config('oxalis.passkey_only', false);

        if ($passkeyOnly && ! in_array($method, ['passkey', 'passkey_recovery', 'smart_dispatch'], true)) {
            return false;
        }

        return match ($method) {
            'passkey' => (bool) config('oxalis.methods.passkey', true),
            'passkey_recovery' => (bool) config('oxalis.methods.passkey', true)
                && (bool) config('oxalis.passkey_recovery.enabled', true),
            'magic_link' => ! $passkeyOnly && (bool) config('oxalis.methods.magic_link', true),
            'email_otp' => ! $passkeyOnly && (bool) config('oxalis.methods.email_otp', true),
            'totp' => ! $passkeyOnly && (bool) config('oxalis.methods.totp', true),
            'password' => ! $passkeyOnly && (bool) config('oxalis.methods.password', true),
            'social' => ! $passkeyOnly && (bool) config('oxalis.methods.social', false),
            'smart_dispatch' => (bool) config('oxalis.smart_dispatch', false),
            'qr_login' => ! $passkeyOnly && (bool) config('oxalis.qr_login.enabled', false),
            'ultrasonic' => ! $passkeyOnly && (bool) config('oxalis.ultrasonic.enabled', false),
            default => false,
        };
    }
}
