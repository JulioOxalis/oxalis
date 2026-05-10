<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Models\AuthEvent;
use Oxalis\Models\Passkey;
use Oxalis\Models\SocialLogin;
use Oxalis\Models\TotpSecret;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $uid  = $user->getAuthIdentifier();

        $passkeys = Passkey::where('user_id', $uid)->latest()->get();

        $totpEnabled = TotpSecret::where('user_id', $uid)
            ->whereNotNull('confirmed_at')
            ->exists();

        $hasRecoveryCodes = TotpSecret::where('user_id', $uid)
            ->whereNotNull('recovery_codes')
            ->where('recovery_codes', '!=', '[]')
            ->where('recovery_codes', '!=', 'null')
            ->exists();

        $socialLogins = SocialLogin::where('user_id', $uid)->get();

        $recentEvents = AuthEvent::where('user_id', $uid)
            ->latest()
            ->take(10)
            ->get();

        return view('oxalis::account.index', compact(
            'passkeys',
            'totpEnabled',
            'hasRecoveryCodes',
            'socialLogins',
            'recentEvents',
        ));
    }
}
