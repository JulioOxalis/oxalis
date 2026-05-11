<?php
namespace Oxalis\Http\Middleware;

use Oxalis\Models\OxalisSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OxalisSessionGuard
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $token = session('oxalis_session_token');

        if (!$token) {
            return $next($request);
        }

        try {
            $session = OxalisSession::findByToken($token);

            if (!$session || (string) $session->user_id !== (string) Auth::id()) {
                Auth::logout();
                $request->session()->invalidate();
                return redirect()->route('oxalis.login')
                    ->with('status', 'Your session was revoked. Please sign in again.');
            }

            // Refresh last active timestamp (throttled to once per minute)
            if (!$session->last_active_at || $session->last_active_at->diffInSeconds(now()) > 60) {
                $session->refreshActivity();
            }
        } catch (\Throwable) {
            // Sessions table missing — skip guard silently
        }

        return $next($request);
    }
}
