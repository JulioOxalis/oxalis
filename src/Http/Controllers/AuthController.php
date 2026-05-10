<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('oxalis::auth.login');
    }

    // ── Step 1: name + email ──────────────────────────────────────────────────

    public function showRegister()
    {
        return view('oxalis::auth.register');
    }

    public function register(Request $request)
    {
        $userModel = config('oxalis.user_model');

        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => ['required', 'email', 'unique:' . (new $userModel)->getTable() . ',email'],
        ]);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        session([
            'oxalis_reg_name'     => $data['name'],
            'oxalis_reg_email'    => $data['email'],
            'oxalis_reg_code'     => bcrypt($code),
            'oxalis_reg_expires'  => now()->addMinutes(10)->timestamp,
            'oxalis_reg_verified' => false,
        ]);

        if (app()->isLocal()) {
            session(['oxalis_reg_dev_code' => $code]);
        }

        try {
            Mail::to($data['email'])->send(new OtpMail($code, 10));
        } catch (\Throwable $e) {
            Log::warning('Oxalis register OTP mail failed', ['email' => $data['email'], 'error' => $e->getMessage()]);
        }

        return redirect()->route('oxalis.register.verify.show');
    }

    // ── Step 2: verify OTP ────────────────────────────────────────────────────

    public function showRegisterVerify()
    {
        if (!session('oxalis_reg_email') || session('oxalis_reg_verified')) {
            return redirect()->route('oxalis.register');
        }

        $hint = app()->isLocal() ? session('oxalis_reg_dev_code') : null;

        return view('oxalis::auth.register-verify', compact('hint'));
    }

    public function registerVerify(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $hash    = session('oxalis_reg_code');
        $expires = session('oxalis_reg_expires');

        if (!$hash || !$expires || now()->timestamp > $expires) {
            session()->forget(['oxalis_reg_name','oxalis_reg_email','oxalis_reg_code','oxalis_reg_expires','oxalis_reg_verified','oxalis_reg_dev_code']);
            return redirect()->route('oxalis.register')
                ->withErrors(['email' => 'Your code expired. Please start again.']);
        }

        if (!password_verify($request->code, $hash)) {
            return back()->withErrors(['code' => 'Incorrect code. Try again.']);
        }

        session(['oxalis_reg_verified' => true]);

        // If password method is disabled, skip step 3 — create user directly
        if (!config('oxalis.methods.password', true)) {
            return $this->createUser(null);
        }

        return redirect()->route('oxalis.register.password.show');
    }

    // ── Step 3: set password ──────────────────────────────────────────────────

    public function showRegisterPassword()
    {
        if (!session('oxalis_reg_verified') || !session('oxalis_reg_email')) {
            return redirect()->route('oxalis.register');
        }

        return view('oxalis::auth.register-password', [
            'name'  => session('oxalis_reg_name'),
            'email' => session('oxalis_reg_email'),
        ]);
    }

    public function registerPassword(Request $request)
    {
        $request->validate(['password' => 'required|min:8|confirmed']);

        if (!session('oxalis_reg_verified') || !session('oxalis_reg_email')) {
            return redirect()->route('oxalis.register');
        }

        return $this->createUser($request->password);
    }

    // ── Shared account creation ───────────────────────────────────────────────

    private function createUser(?string $password): \Illuminate\Http\RedirectResponse
    {
        $name  = session('oxalis_reg_name');
        $email = session('oxalis_reg_email');

        session()->forget(['oxalis_reg_name','oxalis_reg_email','oxalis_reg_code','oxalis_reg_expires','oxalis_reg_verified','oxalis_reg_dev_code']);

        $userModel = config('oxalis.user_model');

        if ($userModel::where('email', $email)->exists()) {
            return redirect()->route('oxalis.register')
                ->withErrors(['email' => 'This email is already registered.']);
        }

        $user = $userModel::create([
            'name'              => $name,
            'email'             => $email,
            'password'          => bcrypt($password ?? Str::random(40)),
            'email_verified_at' => now(),
        ]);

        Auth::login($user);

        $redirect = config('oxalis.methods.passkey', true)
            ? route('oxalis.passkeys.enroll')
            : config('oxalis.routes.home', '/dashboard');

        return redirect($redirect);
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('oxalis.login');
    }
}
