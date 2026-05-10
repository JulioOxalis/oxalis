<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Models\AuthEvent;
use Oxalis\Totp\TotpService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class RecoveryCodeController extends Controller
{
    public function __construct(private TotpService $totp) {}

    public function show()
    {
        return view('oxalis::totp.recovery-codes');
    }

    public function regenerate(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        if (!$this->totp->verify(Auth::user(), $request->code)) {
            return back()->withErrors(['code' => 'Invalid authenticator code. Recovery codes were not regenerated.']);
        }

        $recoveryCodes = $this->totp->generateRecoveryCodes(Auth::user());

        return view('oxalis::totp.recovery-codes', compact('recoveryCodes'));
    }

    public function verify(Request $request)
    {
        $userId = session('oxalis_totp_pending_user_id');

        if (!$userId) {
            return redirect()->route('oxalis.login');
        }

        $request->validate(['recovery_code' => 'required|string']);

        $userModel = config('oxalis.user_model');
        $user = $userModel::find($userId);

        if (!$user) {
            return redirect()->route('oxalis.login');
        }

        if (!$this->totp->verifyRecoveryCode($user, $request->recovery_code)) {
            return back()->withErrors(['recovery_code' => 'Invalid recovery code. Please try again.']);
        }

        $method    = session('oxalis_totp_pending_method', 'password');
        $remember  = (bool) session('oxalis_totp_pending_remember', false);
        $ip        = session('oxalis_totp_pending_ip', $request->ip());
        $userAgent = session('oxalis_totp_pending_user_agent', $request->userAgent());

        session()->forget([
            'oxalis_totp_pending_user_id',
            'oxalis_totp_pending_method',
            'oxalis_totp_pending_remember',
            'oxalis_totp_pending_ip',
            'oxalis_totp_pending_user_agent',
        ]);

        Auth::login($user, $remember);

        AuthEvent::create([
            'user_id'    => $user->getAuthIdentifier(),
            'event'      => 'login',
            'method'     => $method . '+recovery_code',
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'status'     => 'success',
        ]);

        return redirect(config('oxalis.routes.home', '/dashboard'));
    }
}
