<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Models\AuthEvent;
use Oxalis\Models\Passkey;
use Oxalis\Models\TotpSecret;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class TestHubController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(app()->isLocal(), 403, 'Test hub is only available in local development.');

        $user   = Auth::user();
        $origin = config('oxalis.origins')[0] ?? env('APP_URL', 'http://localhost');
        $rpId   = config('oxalis.rp_id', 'localhost');

        $stats = $user ? [
            'passkeys'     => Passkey::where('user_id', $user->getAuthIdentifier())->count(),
            'totp_enabled' => TotpSecret::where('user_id', $user->getAuthIdentifier())->whereNotNull('confirmed_at')->exists(),
            'recent_logins'=> AuthEvent::where('user_id', $user->getAuthIdentifier())->latest()->take(5)->get(),
        ] : null;

        return view('oxalis::dev.test-hub', compact('user', 'origin', 'rpId', 'stats'));
    }
}
