<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Auth\RegistrationPolicy;
use Oxalis\MagicLink\MagicLinkService;
use Oxalis\Models\TotpSecret;
use Oxalis\Models\Passkey;
use Oxalis\WebAuthn\WebAuthnService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SmartDispatchController extends Controller
{
    public function __construct(
        private readonly WebAuthnService  $webAuthn,
        private readonly MagicLinkService $magicLink,
        private readonly RegistrationPolicy $registration,
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
            if (config('oxalis.passkey_only', false) || ! config('oxalis.methods.magic_link', true)) {
                return response()->json([
                    'error' => 'No account exists for this email, and automatic magic-link registration is disabled.',
                ], 422);
            }

            try {
                $this->registration->validateAccountCreationWithoutInvite($request->email);
            } catch (ValidationException $e) {
                return response()->json([
                    'message' => 'Registration is not allowed for this email.',
                    'errors'  => $e->errors(),
                ], 422);
            }

            // Create a stub account (name = email prefix) and send magic link
            $user = $userModel::create([
                'name'              => str($request->email)->before('@')->toString(),
                'email'             => $request->email,
                'password'          => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
            ]);

            $this->magicLink->send($user, $request->ip());

            $devLink = app()->isLocal() ? session('oxalis_dev_magic_link') : null;

            return response()->json([
                'method'   => 'magic_link',
                'message'  => 'No account found — we created one and sent you a sign-in link.',
                'dev_link' => $devLink,
            ]);
        }

        $userId = (string) $user->getAuthIdentifier();

        // ── Has passkeys → passkey ceremony ───────────────────────────────────
        if (Passkey::where('user_id', $userId)->exists()
            && config('oxalis.methods.passkey', true)) {
            try {
                $options = $this->webAuthn->beginAuthentication($user);
            } catch (\Throwable $e) {
                $message = app()->isLocal()
                    ? $e->getMessage()
                    : 'Passkey sign-in unavailable. Try another method or check /oxalis/health/passkeys.';

                return response()->json(['error' => $message], 422);
            }

            session(['oxalis_pending_user_id' => $userId]);

            return response()->json([
                'method'  => 'passkey',
                'options' => $options,
            ]);
        }

        // ── Has TOTP → TOTP form ──────────────────────────────────────────────
        if (TotpSecret::where('user_id', $userId)->whereNotNull('confirmed_at')->exists()
            && ! config('oxalis.passkey_only', false)
            && config('oxalis.methods.totp', true)) {
            session(['oxalis_totp_pending_user_id' => $userId]);

            return response()->json([
                'method'   => 'totp',
                'redirect' => route('oxalis.totp.verify.show'),
            ]);
        }

        // ── Default → magic link ──────────────────────────────────────────────
        if (config('oxalis.passkey_only', false) || ! config('oxalis.methods.magic_link', true)) {
            return response()->json([
                'error' => 'No enabled authentication method is available for this account.',
            ], 422);
        }

        $this->magicLink->send($user, $request->ip());

        $devLink = app()->isLocal() ? session('oxalis_dev_magic_link') : null;

        return response()->json([
            'method'   => 'magic_link',
            'message'  => 'Sign-in link sent to your email.',
            'dev_link' => $devLink,
        ]);
    }
}
