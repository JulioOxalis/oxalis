<?php
namespace Oxalis\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireOxalis
{
    public function handle(Request $request, Closure $next, string $level = 'auth')
    {
        if (!Auth::check()) {
            // redirect()->guest() stores the URL in url.intended so redirect()->intended()
            // in LoginHandler can send the user back after a successful login.
            return redirect()->guest(route('oxalis.login'));
        }

        if ($level === 'passkey' && !Auth::user()->hasPasskeys()) {
            return redirect()->route('oxalis.passkeys.enroll');
        }

        return $next($request);
    }
}
