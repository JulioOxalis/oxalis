<?php
namespace Oxalis\Http\Middleware;

use Oxalis\Models\Passkey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * If the current session was started with a passkey, verify that passkey
 * still exists in the database. If it was revoked/deleted, log the user out.
 *
 * This catches stolen sessions where the device owner deleted the passkey
 * after noticing unauthorized access.
 */
class ValidatePasskeySession
{
    public function handle(Request $request, Closure $next)
    {
        $credentialId = session('oxalis_session_credential_id');

        if ($credentialId && Auth::check()) {
            $exists = Passkey::where('credential_id', $credentialId)
                ->where('user_id', (string) Auth::id())
                ->exists();

            if (!$exists) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('oxalis.login')
                    ->withErrors(['email' => 'Your passkey was revoked. Please sign in again.']);
            }
        }

        return $next($request);
    }
}
