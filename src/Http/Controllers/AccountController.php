<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Models\AuthEvent;
use Oxalis\Models\Lockout;
use Oxalis\Models\MagicLink;
use Oxalis\Models\OtpChallenge;
use Oxalis\Models\Passkey;
use Oxalis\Models\PasskeyRecoveryCode;
use Oxalis\Models\SocialLogin;
use Oxalis\Models\TotpSecret;
use Oxalis\Models\TotpTrustedDevice;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $uid  = $user->getAuthIdentifier();

        $passkeys = Passkey::where('user_id', $uid)->latest()->get();
        $passkeyRecoveryCount = PasskeyRecoveryCode::where('user_id', $uid)
            ->whereNull('used_at')
            ->count();

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
            'passkeyRecoveryCount',
            'totpEnabled',
            'hasRecoveryCodes',
            'socialLogins',
            'recentEvents',
        ));
    }

    public function deleteAccount(Request $request)
    {
        abort_unless(config('oxalis.account_deletion.enabled', true), 404);

        $request->validate(['confirm_email' => 'required|email']);

        $user = Auth::user();

        if ($request->confirm_email !== $user->email) {
            return back()->withErrors(['confirm_email' => 'Email does not match your account.']);
        }

        $uid = $user->getAuthIdentifier();

        // Delete all oxalis data for this user
        Passkey::where('user_id', $uid)->delete();
        PasskeyRecoveryCode::where('user_id', $uid)->delete();
        TotpSecret::where('user_id', $uid)->delete();
        OtpChallenge::where('user_id', $uid)->delete();
        MagicLink::where('user_id', $uid)->delete();
        SocialLogin::where('user_id', $uid)->delete();
        AuthEvent::where('user_id', $uid)->delete();
        TotpTrustedDevice::where('user_id', $uid)->delete();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Optionally delete the user model itself
        if (config('oxalis.account_deletion.delete_user_model', true)) {
            $user->delete();
        }

        return redirect()->route('oxalis.login')
            ->with('status', 'Your account has been deleted.');
    }
}
