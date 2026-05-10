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
            return redirect()->route('oxalis.login')
                ->with('intended', $request->url());
        }

        if ($level === 'passkey' && !Auth::user()->hasPasskeys()) {
            return redirect()->route('oxalis.passkeys.enroll');
        }

        return $next($request);
    }
}
