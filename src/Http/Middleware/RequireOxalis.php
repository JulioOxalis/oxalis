<?php
namespace Oxalis\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Oxalis\Facades\Oxalis;

class RequireOxalis
{
    public function handle(Request $request, Closure $next, string $level = 'auth')
    {
        if (! Auth::check()) {
            return redirect()->guest(route('oxalis.login'));
        }

        if ($level === 'passkey') {
            $user = Auth::user();
            $has  = method_exists($user, 'hasPasskeys')
                ? $user->hasPasskeys()
                : Oxalis::hasPasskeys($user);

            if (! $has) {
                return redirect()->route('oxalis.passkeys.enroll');
            }
        }

        return $next($request);
    }
}
