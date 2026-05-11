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
        $user = $userModel::where('email', $request->email)->first();

        $options = oxalis::beginAuthentication($user ?: null);

        if ($user) {
            session(['oxalis_pending_user_id' => $user->getAuthIdentifier()]);
        }

        return response()->json($options);
    }

    public function finishAuthentication(Request $request)
    {
        try {
            $user = oxalis::finishAuthentication($request->all());
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Authentication failed'], 422);
        }

        if (!$user) {
            return response()->json(['error' => 'Credential not recognised'], 422);
        }

        Auth::login($user, true);

        // Stamp the credential ID into the session so ValidatePasskeySession
        // middleware can detect if the passkey is later revoked.
        session(['oxalis_session_credential_id' => base64_encode(base64_decode($request->input('rawId', '')))]);

        AuthEvent::create([
            'user_id'    => $user->getAuthIdentifier(),
            'event'      => 'login',
            'method'     => 'passkey',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status'     => 'success',
        ]);

        return response()->json(['redirect' => config('oxalis.routes.home', '/dashboard')]);
    }

    // ── Guest: conditional (autofill) begin ─────────────────────────────────
    // No email required — browser shows passkeys in the autofill dropdown.
    // Uses WebAuthn discoverable credentials (empty allowCredentials).

    public function beginAutofill()
    {
        $options = oxalis::beginAuthentication(null);
        return response()->json($options);
    }

    // ── Authenticated: enrollment ────────────────────────────────────────────

    public function showEnroll()
    {
        return view('oxalis::passkeys.enroll');
    }

    public function showManage()
    {
        $passkeys = Auth::user()->passkeys()->latest()->get();
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
            $passkey = oxalis::finishRegistration(Auth::user(), $request->all());
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Registration failed: '.$e->getMessage()], 422);
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

        $user = Auth::user();
        $count = Passkey::where('user_id', $user->getAuthIdentifier())->count();

        if ($count <= 1) {
            return back()->withErrors(['id' => 'You must keep at least one passkey.']);
        }

        Passkey::where('id', $request->id)
            ->where('user_id', $user->getAuthIdentifier())
            ->delete();

        return back()->with('status', 'Passkey removed.');
    }
}
