<?php
namespace Oxalis\Http\Middleware;

use Oxalis\Models\Lockout;
use Closure;
use Illuminate\Http\Request;

/**
 * IP-based rate limiter backed by the oxalis_lockouts table.
 * Works with any database (MySQL, SQLite, MongoDB) — no cache dependency.
 *
 * Usage in routes:
 *   ->middleware('oxalis.ip:30,1')   → 30 hits per 1 minute per IP
 *   ->middleware('oxalis.ip:5,5')    → 5 hits per 5 minutes per IP (for send endpoints)
 */
class OxalisIpThrottle
{
    public function handle(Request $request, Closure $next, int $maxHits = 30, int $decayMinutes = 1)
    {
        $key     = 'ip:' . $decayMinutes . ':' . hash('sha256', $request->ip());
        $lockout = Lockout::firstOrCreate(['key' => $key], ['attempts' => 0]);

        // Reset counter if the window has expired
        if ($lockout->locked_until && $lockout->locked_until->isPast()) {
            $lockout->update(['attempts' => 0, 'locked_until' => null]);
            $lockout->refresh();
        }

        if ($lockout->attempts >= $maxHits) {
            $seconds = $lockout->locked_until
                ? (int) now()->diffInSeconds($lockout->locked_until)
                : $decayMinutes * 60;

            return $this->tooManyAttempts($request, $seconds);
        }

        $lockout->increment('attempts');

        // Set the window expiry on the first hit
        if ($lockout->attempts === 1) {
            $lockout->update(['locked_until' => now()->addMinutes($decayMinutes)]);
        }

        $response = $next($request);

        // Add standard rate-limit headers
        $remaining = max(0, $maxHits - $lockout->attempts);
        if (method_exists($response, 'header')) {
            $response->header('X-RateLimit-Limit',     $maxHits);
            $response->header('X-RateLimit-Remaining', $remaining);
        }

        return $response;
    }

    private function tooManyAttempts(Request $request, int $retryAfter)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error'       => 'Too many requests. Please try again later.',
                'retry_after' => $retryAfter,
            ], 429);
        }

        return back()->withErrors(['email' => "Too many attempts. Please wait {$retryAfter} seconds."]);
    }
}
