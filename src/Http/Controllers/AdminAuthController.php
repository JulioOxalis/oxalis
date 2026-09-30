<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Models\AdminCredential;
use Oxalis\Models\Lockout;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class AdminAuthController extends Controller
{
    private const LOCKOUT_KEY      = 'oxalis_admin_login';
    private const LOCKOUT_MAX      = 5;
    private const LOCKOUT_MINUTES  = 30;
    private const SESSION_KEY      = 'oxalis_admin_authenticated';
    private const SESSION_AT       = 'oxalis_admin_at';
    private const SESSION_VERSION  = 'oxalis_admin_version';

    // ── First-time setup ──────────────────────────────────────────────────────

    public function showSetup()
    {
        abort_unless(config('oxalis.admin.enabled', false), 404);

        if (AdminCredential::isSetup()) {
            return redirect()->route('oxalis.admin.login');
        }

        // Generate a TOTP secret to show QR code on setup page
        $g2fa   = new Google2FA();
        $secret = $g2fa->generateSecretKey(32);
        $qrUri  = $g2fa->getQRCodeUrl(config('app.name').' Admin', 'oxalis-admin', $secret);

        session(['oxalis_admin_setup_secret' => $secret]);

        return view('oxalis::admin.setup', compact('secret', 'qrUri'));
    }

    public function setup(Request $request)
    {
        abort_unless(config('oxalis.admin.enabled', false), 404);

        if (AdminCredential::isSetup()) {
            return redirect()->route('oxalis.admin.login');
        }

        $request->validate([
            'password'              => 'required|min:12|confirmed',
            'enable_totp'           => 'nullable|boolean',
            'totp_code'             => 'required_if:enable_totp,1|nullable|digits:6',
        ]);

        $totpSecret    = null;
        $totpConfirmed = null;

        if ($request->boolean('enable_totp')) {
            $secret = session('oxalis_admin_setup_secret');
            $g2fa   = new Google2FA();

            if (!$secret || !$g2fa->verifyKey($secret, $request->totp_code ?? '')) {
                return back()->withErrors(['totp_code' => 'Incorrect authenticator code. Please try again.']);
            }

            $totpSecret    = $secret;
            $totpConfirmed = now();
        }

        session()->forget('oxalis_admin_setup_secret');

        AdminCredential::create([
            'password_hash'     => Hash::make($request->password),
            'totp_secret'       => $totpSecret,
            'totp_confirmed_at' => $totpConfirmed,
            'session_version'   => Str::random(32),
        ]);

        return redirect()->route('oxalis.admin.login')
            ->with('admin_success', 'Admin access configured. Sign in below.');
    }

    // ── Login ─────────────────────────────────────────────────────────────────

    public function showLogin()
    {
        abort_unless(config('oxalis.admin.enabled', false), 404);

        if (!AdminCredential::isSetup()) {
            return redirect()->route('oxalis.admin.setup');
        }

        if (session(self::SESSION_KEY)) {
            return redirect()->route('oxalis.admin');
        }

        $cred      = AdminCredential::first();
        $lockoutKey = self::LOCKOUT_KEY . ':' . request()->ip();

        $lockedUntil = null;
        try {
            $lockout = Lockout::where('key', $lockoutKey)->first();
            if ($lockout?->isLocked()) {
                $lockedUntil = $lockout->locked_until;
            }
        } catch (\Throwable) {}

        return view('oxalis::admin.login', compact('cred', 'lockedUntil'));
    }

    public function login(Request $request)
    {
        abort_unless(config('oxalis.admin.enabled', false), 404);

        $request->validate(['password' => 'required']);

        $lockoutKey = self::LOCKOUT_KEY . ':' . $request->ip();

        // Check lockout
        try {
            $lockout = Lockout::firstOrCreate(['key' => $lockoutKey], ['attempts' => 0]);
            if ($lockout->isLocked()) {
                return back()->with('admin_error', "Too many failed attempts. Try again in {$lockout->secondsRemaining()} seconds.");
            }
        } catch (\Throwable) {}

        $cred = AdminCredential::first();

        if (!$cred || !Hash::check($request->password, $cred->password_hash)) {
            $this->incrementLockout($lockoutKey);
            return back()->with('admin_error', 'Incorrect password.');
        }

        // TOTP check
        if ($cred->hasTotpEnabled()) {
            $request->validate(['totp_code' => 'required|digits:6']);
            $g2fa = new Google2FA();
            if (!$g2fa->verifyKey($cred->totp_secret, $request->totp_code)) {
                $this->incrementLockout($lockoutKey);
                return back()->with('admin_error', 'Incorrect authenticator code.')->withInput(['show_totp' => true]);
            }
        }

        // Success — reset lockout, set session
        try {
            Lockout::where('key', $lockoutKey)->update(['attempts' => 0, 'locked_until' => null]);
        } catch (\Throwable) {}

        $cred->recordLogin($request->ip());

        session([
            self::SESSION_KEY     => true,
            self::SESSION_AT      => now()->timestamp,
            self::SESSION_VERSION => $cred->session_version,
        ]);

        return redirect()->route('oxalis.admin');
    }

    public function logout(Request $request)
    {
        session()->forget([self::SESSION_KEY, self::SESSION_AT, self::SESSION_VERSION]);
        return redirect()->route('oxalis.admin.login')
            ->with('admin_success', 'Signed out successfully.');
    }

    // ── Change password ───────────────────────────────────────────────────────

    public function showChangePassword()
    {
        return view('oxalis::admin.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:12|confirmed',
        ]);

        $cred = AdminCredential::first();

        if (!Hash::check($request->current_password, $cred->password_hash)) {
            return back()->withErrors(['current_password' => 'Incorrect current password.']);
        }

        $cred->update([
            'password_hash'   => Hash::make($request->password),
            'session_version' => Str::random(32), // invalidates all other sessions
        ]);

        // Re-stamp own session with new version
        session([self::SESSION_VERSION => $cred->fresh()->session_version]);

        return back()->with('admin_success', 'Password updated. All other sessions have been invalidated.');
    }

    private function incrementLockout(string $key): void
    {
        try {
            $lockout  = Lockout::firstOrCreate(['key' => $key], ['attempts' => 0]);
            $attempts = $lockout->attempts + 1;
            $lockedUntil = $attempts >= self::LOCKOUT_MAX
                ? now()->addMinutes(self::LOCKOUT_MINUTES)
                : null;
            $lockout->update(['attempts' => $attempts, 'locked_until' => $lockedUntil]);
        } catch (\Throwable) {}
    }
}
