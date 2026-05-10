<?php
namespace Oxalis\Http\Middleware;

use Oxalis\Models\Lockout;
use Closure;
use Illuminate\Http\Request;

class OxalisThrottle
{
    private int $maxAttempts;
    private int $lockoutMinutes;

    public function __construct()
    {
        $this->maxAttempts    = config('oxalis.lockout.max_attempts', 5);
        $this->lockoutMinutes = config('oxalis.lockout.minutes', 15);
    }

    public function handle(Request $request, Closure $next)
    {
        $email = $request->input('email', '');
        $key   = Lockout::keyFor($email, $request->ip());

        $lockout = Lockout::firstOrCreate(['key' => $key], ['attempts' => 0]);

        if ($lockout->isLocked()) {
            return back()->withErrors([
                'email' => "Too many attempts. Try again in {$lockout->secondsRemaining()} seconds.",
            ])->with('lockout_seconds', $lockout->secondsRemaining());
        }

        $response = $next($request);

        // Increment on failed login (controller must put 'oxalis_login_failed' in session)
        if (session()->pull('oxalis_login_failed', false)) {
            $attempts = $lockout->attempts + 1;
            $lockedUntil = $attempts >= $this->maxAttempts
                ? now()->addMinutes($this->lockoutMinutes)
                : null;

            $lockout->update(['attempts' => $attempts, 'locked_until' => $lockedUntil]);
        }

        // Reset on successful login
        if (session()->pull('oxalis_login_success', false)) {
            $lockout->update(['attempts' => 0, 'locked_until' => null]);
        }

        return $response;
    }
}
