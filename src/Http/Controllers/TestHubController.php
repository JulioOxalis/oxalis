<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Models\AuthEvent;
use Oxalis\Models\Passkey;
use Oxalis\Models\TotpSecret;
use Oxalis\Support\WebAuthnConfig;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class TestHubController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(app()->isLocal(), 403, 'Test hub is only available in local development.');

        $user        = Auth::user();
        $origins     = array_values(array_filter((array) config('oxalis.origins', [])));
        $rpId        = config('oxalis.rp_id', 'localhost');
        $browser     = $request->getSchemeAndHttpHost();
        $originOk    = WebAuthnConfig::originMatchesRequest($origins, $request);
        $rpOk        = WebAuthnConfig::rpIdMatchesRequest($rpId, $request);
        $suggested   = WebAuthnConfig::suggestedOrigins(null, $request);

        $stats = $user ? [
            'passkeys'     => Passkey::where('user_id', (string) $user->getAuthIdentifier())->count(),
            'totp_enabled' => TotpSecret::where('user_id', (string) $user->getAuthIdentifier())->whereNotNull('confirmed_at')->exists(),
            'recent_logins'=> AuthEvent::where('user_id', (string) $user->getAuthIdentifier())->latest()->take(5)->get(),
        ] : null;

        return view('oxalis::dev.test-hub', compact(
            'user', 'origins', 'rpId', 'stats', 'browser', 'originOk', 'rpOk', 'suggested'
        ));
    }
}
