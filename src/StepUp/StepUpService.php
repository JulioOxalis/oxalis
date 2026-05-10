<?php
namespace Oxalis\StepUp;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class StepUpService
{
    private const SESSION_KEY = 'oxalis_step_up';

    /** Mark the current session as step-up verified right now. */
    public function markVerified(): void
    {
        Session::put(self::SESSION_KEY, now()->timestamp);
    }

    /** Check if step-up was verified within the configured TTL. */
    public function isVerified(): bool
    {
        $at  = Session::get(self::SESSION_KEY);
        $ttl = config('oxalis.step_up.ttl_minutes', 15) * 60;

        return $at !== null && (now()->timestamp - $at) < $ttl;
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /** Store where the user was trying to go, then redirect to the step-up prompt. */
    public function challenge(string $intendedUrl): RedirectResponse
    {
        Session::put('oxalis_step_up_intended', $intendedUrl);

        return redirect()->route('oxalis.step-up.prompt');
    }

    public function intendedUrl(string $fallback = '/'): string
    {
        return Session::pull('oxalis_step_up_intended', $fallback);
    }
}
