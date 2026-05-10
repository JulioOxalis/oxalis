<?php
namespace Oxalis\Http\Controllers;

use Oxalis\MagicLink\MagicLinkService;
use Oxalis\Models\TotpSecret;
use Oxalis\Models\Passkey;
use Oxalis\WebAuthn\WebAuthnService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SmartDispatchController extends Controller
{
    public function __construct(
        private readonly WebAuthnService  $webAuthn,
        private readonly MagicLinkService $magicLink,
    ) {}

    /** The one-field login page. */
    public function show()
    {
        return view('oxalis::auth.dispatch');
    }

    /**
     * AJAX: given an email, return which method to use and the necessary options.
     * Called as the user finishes typing (on blur / submit).
     */
    public function dispatch(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $userModel = config('oxalis.user_model');
        $user = $userModel::where('email', $request->email)->first();

        // ── No account found → auto-register via magic link ───────────────────
        if (!$user) {
            // Create a stub account (name = email prefix) and send magic link
            $user = $userModel::create([
                'name'  => str($request->email)->before('@')->toString(),
                'email' => $request->email,
            ]);

            $this->magicLink->send($user, $request->ip());

            $devLink = app()->isLocal() ? session('oxalis_dev_magic_link') : null;

            return response()->json([
                'method'   => 'magic_link',
                'message'  => 'No account found — we created one and sent you a sign-in link.',
                'dev_link' => $devLink,
            ]);
        }

        // ── Has passkeys → passkey ceremony ───────────────────────────────────
        if (Passkey::where('user_id', $user->getAuthIdentifier())->exists()
            && config('oxalis.methods.passkey', true)) {
            $options = $this->webAuthn->beginAuthentication($user);

            return response()->json([
                'method'  => 'passkey',
                'options' => $options,
            ]);
        }

        // ── Has TOTP → TOTP form ──────────────────────────────────────────────
        if (TotpSecret::where('user_id', $user->getAuthIdentifier())->whereNotNull('confirmed_at')->exists()
            && config('oxalis.methods.totp', true)) {
            session(['oxalis_totp_pending_user_id' => $user->getAuthIdentifier()]);

            return response()->json([
                'method'   => 'totp',
                'redirect' => route('oxalis.totp.verify.show'),
            ]);
        }

        // ── Default → magic link ──────────────────────────────────────────────
        $this->magicLink->send($user, $request->ip());

        $devLink = app()->isLocal() ? session('oxalis_dev_magic_link') : null;

        return response()->json([
            'method'   => 'magic_link',
            'message'  => 'Sign-in link sent to your email.',
            'dev_link' => $devLink,
        ]);
    }
}
