<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Events\PasskeyRegistered;
use Oxalis\Facades\oxalis;
use Oxalis\Models\AuthEvent;
use Oxalis\Models\Passkey;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class PasskeyController extends Controller
{
    // ── Guest: begin authentication ──────────────────────────────────────────

    public function beginAuthentication(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $userModel = config('oxalis.user_model');
        $user      = $userModel::where('email', $request->email)->first();

        if ($user && ! oxalis::hasPasskeys($user)) {
            return response()->json([
                'error'      => 'No passkey registered for this account. Sign in with another method, then add a passkey from your account settings.',
                'enroll_url' => route('oxalis.passkeys.enroll'),
                'code'       => 'no_passkey',
            ], 422);
        }

        try {
            $options = oxalis::beginAuthentication($user ?: null);
        } catch (\Throwable $e) {
            return $this->passkeyError($e, 422);
        }

        if ($user) {
            session(['oxalis_pending_user_id' => $user->getAuthIdentifier()]);
        }

        return response()->json($options);
    }

    public function finishAuthentication(Request $request)
    {
        $credentialId = $request->input('id', '');
        $lockKey      = 'passkey_auth:'.hash('sha256', $credentialId);

        try {
            $lockout = \Oxalis\Models\Lockout::firstOrCreate(['key' => $lockKey], ['attempts' => 0]);
            if ($lockout->isLocked()) {
                return response()->json(['error' => 'Too many failed attempts. Try again later.'], 429);
            }
        } catch (\Throwable) {
        }

        try {
            $result = oxalis::finishAuthentication($request->all(), $request->getHost());
            $user   = $result['user'];
            $passkey = $result['passkey'];
        } catch (\Throwable $e) {
            try {
                $lockout = \Oxalis\Models\Lockout::firstOrCreate(['key' => $lockKey], ['attempts' => 0]);
                $attempts = $lockout->attempts + 1;
                $lockout->update([
                    'attempts'     => $attempts,
                    'locked_until' => $attempts >= 5 ? now()->addMinutes(15) : null,
                ]);
            } catch (\Throwable) {
            }

            return $this->passkeyError($e, 422);
        }

        try {
            \Oxalis\Models\Lockout::where('key', $lockKey)->update(['attempts' => 0, 'locked_until' => null]);
        } catch (\Throwable) {
        }

        Auth::login($user, true);

        session(['oxalis_session_credential_id' => $passkey->credential_id]);

        AuthEvent::create([
            'user_id'    => (string) $user->getAuthIdentifier(),
            'event'      => 'login',
            'method'     => 'passkey',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status'     => 'success',
        ]);

        return response()->json(['redirect' => config('oxalis.routes.home', '/dashboard')]);
    }

    public function beginAutofill()
    {
        try {
            $options = oxalis::beginAuthentication(null);
        } catch (\Throwable $e) {
            return $this->passkeyError($e, 422);
        }

        return response()->json($options);
    }

    // ── Authenticated: enrollment ────────────────────────────────────────────

    public function showEnroll()
    {
        return view('oxalis::passkeys.enroll');
    }

    public function showManage()
    {
        $passkeys = Passkey::where('user_id', Auth::id())->latest()->get();

        return view('oxalis::passkeys.manage', compact('passkeys'));
    }

    public function beginRegistration(Request $request)
    {
        $request->validate(['label' => 'nullable|string|max:100']);

        $options = oxalis::beginRegistration(
            Auth::user(),
            $request->input('label', 'My Passkey'),
        );

        return response()->json($options);
    }

    public function finishRegistration(Request $request)
    {
        try {
            $passkey = oxalis::finishRegistration(Auth::user(), $request->all(), $request->getHost());
        } catch (\Throwable $e) {
            return $this->passkeyError($e, 422, 'Registration failed');
        }

        event(new PasskeyRegistered(Auth::user(), $passkey));

        return response()->json(['message' => 'Passkey registered', 'passkey' => $passkey]);
    }

    public function rename(Request $request)
    {
        $request->validate(['id' => 'required', 'label' => 'required|string|max:100']);

        Passkey::where('id', $request->id)
            ->where('user_id', Auth::id())
            ->update(['label' => $request->label]);

        return back()->with('status', 'Passkey renamed.');
    }

    public function delete(Request $request)
    {
        $request->validate(['id' => 'required']);

        $user  = Auth::user();
        $count = Passkey::where('user_id', $user->getAuthIdentifier())->count();

        if ($count <= 1) {
            return back()->withErrors(['id' => 'You must keep at least one passkey.']);
        }

        Passkey::where('id', $request->id)
            ->where('user_id', $user->getAuthIdentifier())
            ->delete();

        return back()->with('status', 'Passkey removed.');
    }

    private function passkeyError(\Throwable $e, int $status, ?string $prefix = null): \Illuminate\Http\JsonResponse
    {
        $message = $prefix ? $prefix.': '.$e->getMessage() : $e->getMessage();

        if (! app()->isLocal()) {
            $message = $prefix
                ? ($prefix.'. Please check OXALIS_RP_ID and OXALIS_ORIGINS match your URL.')
                : 'Authentication failed. Please check OXALIS_RP_ID and OXALIS_ORIGINS match your URL.';
        }

        return response()->json(['error' => $message], $status);
    }
}
