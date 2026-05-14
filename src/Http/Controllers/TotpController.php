<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Auth\LoginHandler;
use Oxalis\Models\Lockout;
use Oxalis\Models\TotpTrustedDevice;
use Oxalis\Totp\TotpService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TotpController extends Controller
{
    public function __construct(
        private readonly TotpService  $totp,
        private readonly LoginHandler $login,
    ) {}

    // ── Setup (authenticated) ────────────────────────────────────────────────

    public function showSetup()
    {
        if ($this->totp->isEnabled(Auth::user())) {
            return redirect()->route('oxalis.totp.manage');
        }

        ['secret' => $secret, 'qr_uri' => $qrUri] = $this->totp->beginSetup(Auth::user());

        return view('oxalis::totp.setup', compact('secret', 'qrUri'));
    }

    public function confirmSetup(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $user = Auth::user();

        if (!$this->totp->confirmSetup($user, $request->code)) {
            return back()->withErrors(['code' => 'Incorrect code. Make sure your authenticator app clock is in sync.']);
        }

        $recoveryCodes = $this->totp->generateRecoveryCodes($user);

        return view('oxalis::totp.setup-complete', compact('recoveryCodes'));
    }

    public function showManage()
    {
        $enabled = $this->totp->isEnabled(Auth::user());
        return view('oxalis::totp.manage', compact('enabled'));
    }

    public function disable(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        if (!$this->totp->verify(Auth::user(), $request->code)) {
            return back()->withErrors(['code' => 'Incorrect code. TOTP was not disabled.']);
        }

        $this->totp->disable(Auth::user());

        return redirect()->route('oxalis.totp.manage')
            ->with('status', 'Authenticator app removed.');
    }

    // ── Verification during login (pending TOTP challenge) ───────────────────

    public function showVerify()
    {
        if (!session('oxalis_totp_pending_user_id')) {
            return redirect()->route('oxalis.login');
        }

        return view('oxalis::totp.verify');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $userId    = session('oxalis_totp_pending_user_id');
        $userModel = config('oxalis.user_model');
        $user      = $userModel::find($userId);

        if (!$user) {
            return redirect()->route('oxalis.login');
        }

        // Per-user TOTP lockout — IP throttle alone is insufficient because a
        // distributed attacker can rotate IPs while targeting a single account.
        $lockKey = 'totp_login:' . hash('sha256', (string) $userId);
        try {
            $lockout = Lockout::firstOrCreate(['key' => $lockKey], ['attempts' => 0]);
            if ($lockout->isLocked()) {
                return back()->withErrors(['code' => 'Too many failed attempts. Please try again later.']);
            }
        } catch (\Throwable) {}

        if (!$this->totp->verify($user, $request->code)) {
            try {
                $attempts = ($lockout ?? null)?->attempts + 1 ?? 1;
                ($lockout ?? null)?->update([
                    'attempts'     => $attempts,
                    'locked_until' => $attempts >= 5 ? now()->addMinutes(15) : null,
                ]);
            } catch (\Throwable) {}
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        // Reset lockout on success
        try { ($lockout ?? null)?->update(['attempts' => 0, 'locked_until' => null]); } catch (\Throwable) {}

        // Retrieve context stored by LoginHandler (original method, remember preference)
        $method    = session('oxalis_totp_pending_method', 'totp');
        $remember  = (bool) session('oxalis_totp_pending_remember', true);
        $ip        = session('oxalis_totp_pending_ip', $request->ip());
        $userAgent = session('oxalis_totp_pending_user_agent', $request->userAgent());

        session()->forget([
            'oxalis_totp_pending_user_id',
            'oxalis_totp_pending_method',
            'oxalis_totp_pending_remember',
            'oxalis_totp_pending_ip',
            'oxalis_totp_pending_user_agent',
        ]);

        $response = $this->login->loginAfterTotp(
            user:      $user,
            method:    $method,
            ip:        $ip,
            userAgent: $userAgent,
            remember:  $remember,
        );

        // Set a trusted device cookie if the user opted in
        if (config('oxalis.totp_trust.enabled', true) && $request->boolean('remember_device')) {
            try {
                $token = Str::random(64);
                TotpTrustedDevice::createForUser(
                    userId:    $user->getAuthIdentifier(),
                    token:     $token,
                    ip:        $request->ip(),
                    userAgent: $request->userAgent() ?? '',
                );
                $days = config('oxalis.totp_trust.days', 30);
                // SameSite=Strict prevents the cookie being sent on cross-site requests.
                $response->withCookie(cookie('oxalis_totp_trust', $token, $days * 1440, '/', null, true, true, false, 'strict'));
            } catch (\Throwable) {}
        }

        return $response;
    }
}
