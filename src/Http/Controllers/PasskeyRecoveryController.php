<?php
namespace Oxalis\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Oxalis\Models\AuthEvent;
use Oxalis\Passkeys\PasskeyRecoveryService;

class PasskeyRecoveryController extends Controller
{
    public function __construct(private PasskeyRecoveryService $recovery) {}

    public function showManage()
    {
        abort_unless(config('oxalis.passkey_recovery.enabled', true), 404);

        $user = Auth::user();
        $activeCount = $this->recovery->activeCount($user);
        $recoveryCodes = session()->pull('oxalis_passkey_recovery_codes');

        return view('oxalis::passkeys.recovery-codes', compact('activeCount', 'recoveryCodes'));
    }

    public function regenerate()
    {
        abort_unless(config('oxalis.passkey_recovery.enabled', true), 404);

        $recoveryCodes = $this->recovery->generate(
            Auth::user(),
            (int) config('oxalis.passkey_recovery.codes', 8),
        );

        session(['oxalis_passkey_recovery_codes' => $recoveryCodes]);

        return redirect()->route('oxalis.passkeys.recovery');
    }

    public function showRecover()
    {
        abort_unless(config('oxalis.passkey_recovery.enabled', true), 404);

        return view('oxalis::passkeys.recover');
    }

    public function recover(Request $request)
    {
        abort_unless(config('oxalis.passkey_recovery.enabled', true), 404);

        $request->validate([
            'email' => 'required|email',
            'recovery_code' => 'required|string',
        ]);

        $userModel = config('oxalis.user_model');
        $user = $userModel::where('email', $request->email)->first();

        if (!$user || !$this->recovery->consume($user, $request->recovery_code)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['recovery_code' => 'Invalid recovery code.']);
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        session(['oxalis_passkey_recovered' => true]);

        AuthEvent::create([
            'user_id'    => (string) $user->getAuthIdentifier(),
            'event'      => 'login',
            'method'     => 'passkey_recovery_code',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status'     => 'success',
        ]);

        return redirect()->route('oxalis.passkeys.enroll')
            ->with('status', 'Recovery code accepted. Add a new passkey now.');
    }
}
