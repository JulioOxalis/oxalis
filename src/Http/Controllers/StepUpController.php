<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Events\StepUpVerified;
use Oxalis\StepUp\StepUpService;
use Oxalis\Totp\TotpService;
use Oxalis\WebAuthn\WebAuthnService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class StepUpController extends Controller
{
    public function __construct(
        private readonly StepUpService  $stepUp,
        private readonly TotpService    $totp,
        private readonly WebAuthnService $webAuthn,
    ) {}

    public function prompt()
    {
        $user        = Auth::user();
        $hasTotp     = $this->totp->isEnabled($user);
        $hasPasskey  = $this->webAuthn->hasPasskeys($user);

        if (! $hasTotp && ! $hasPasskey) {
            $this->stepUp->markVerified();

            return redirect($this->stepUp->intendedUrl(config('oxalis.routes.home', '/dashboard')));
        }

        $passkeyOptions = $hasPasskey ? $this->webAuthn->beginAuthentication($user) : null;

        return view('oxalis::step-up.prompt', compact('hasTotp', 'hasPasskey', 'passkeyOptions'));
    }

    public function verifyTotp(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        if (! $this->totp->verify(Auth::user(), $request->code)) {
            return back()->withErrors(['code' => 'Incorrect code. Please try again.']);
        }

        $this->stepUp->markVerified();
        event(new StepUpVerified(Auth::user(), 'totp'));

        return redirect($this->stepUp->intendedUrl(config('oxalis.routes.home', '/dashboard')));
    }

    public function verifyPasskey(Request $request)
    {
        try {
            $result = $this->webAuthn->finishAuthentication($request->all(), $request->getHost());
            $user   = $result['user'];
        } catch (\Throwable $e) {
            $message = app()->isLocal()
                ? 'Passkey verification failed: '.$e->getMessage()
                : 'Passkey verification failed.';

            return response()->json(['error' => $message], 422);
        }

        if ($user->getAuthIdentifier() !== Auth::id()) {
            return response()->json(['error' => 'Passkey does not belong to this account.'], 422);
        }

        $this->stepUp->markVerified();
        event(new StepUpVerified(Auth::user(), 'passkey'));

        return response()->json(['redirect' => $this->stepUp->intendedUrl(config('oxalis.routes.home', '/dashboard'))]);
    }
}
