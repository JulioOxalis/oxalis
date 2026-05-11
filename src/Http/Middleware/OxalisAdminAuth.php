<?php
namespace Oxalis\Http\Middleware;

use Oxalis\Models\AdminCredential;
use Closure;
use Illuminate\Http\Request;

class OxalisAdminAuth
{
    private const SESSION_KEY     = 'oxalis_admin_authenticated';
    private const SESSION_AT      = 'oxalis_admin_at';
    private const SESSION_VERSION = 'oxalis_admin_version';
    private const TIMEOUT         = 7200; // 2 hours

    public function handle(Request $request, Closure $next)
    {
        abort_unless(config('oxalis.admin.enabled', false), 404);

        // First-time setup
        if (!AdminCredential::isSetup()) {
            if (!$request->routeIs('oxalis.admin.setup', 'oxalis.admin.setup.post')) {
                return redirect()->route('oxalis.admin.setup');
            }
            return $next($request);
        }

        // Allow login/logout routes through
        if ($request->routeIs('oxalis.admin.login', 'oxalis.admin.login.post', 'oxalis.admin.logout')) {
            return $next($request);
        }

        // Check authenticated session
        if (!session(self::SESSION_KEY)) {
            return redirect()->route('oxalis.admin.login');
        }

        // Session timeout
        $at = session(self::SESSION_AT);
        if (!$at || (now()->timestamp - $at) > self::TIMEOUT) {
            session()->forget([self::SESSION_KEY, self::SESSION_AT, self::SESSION_VERSION]);
            return redirect()->route('oxalis.admin.login')
                ->with('admin_error', 'Session expired. Please sign in again.');
        }

        // Session version check — invalidated when password changes
        $cred = AdminCredential::first();
        if ($cred && session(self::SESSION_VERSION) !== $cred->session_version) {
            session()->forget([self::SESSION_KEY, self::SESSION_AT, self::SESSION_VERSION]);
            return redirect()->route('oxalis.admin.login')
                ->with('admin_error', 'Credentials changed. Please sign in again.');
        }

        // Refresh session timestamp on every request (sliding window)
        session([self::SESSION_AT => now()->timestamp]);

        return $next($request);
    }
}
