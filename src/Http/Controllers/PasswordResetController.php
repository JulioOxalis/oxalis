<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Mail\PasswordResetMail;
use Oxalis\Models\OxalisSession;
use Oxalis\Models\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function showForgot()
    {
        return view('oxalis::auth.forgot-password');
    }

    public function sendLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $userModel = config('oxalis.user_model');
        $user = $userModel::where('email', $request->email)->first();

        if ($user) {
            // Invalidate existing tokens
            PasswordReset::where('user_id', $user->getAuthIdentifier())
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            $token = Str::random(64);

            PasswordReset::create([
                'user_id'    => $user->getAuthIdentifier(),
                'token'      => $token,
                'expires_at' => now()->addMinutes(60),
                'ip_address' => $request->ip(),
            ]);

            $url = route('oxalis.password.reset.show', ['token' => $token]);

            if (app()->isLocal()) {
                session(['oxalis_dev_reset_link' => $url]);
            }

            $this->sendMail($user->email, $url);
        }

        $devLink = app()->isLocal() ? session('oxalis_dev_reset_link') : null;

        return view('oxalis::auth.forgot-password-sent', [
            'email'   => $request->email,
            'devLink' => $devLink,
        ]);
    }

    public function showReset(string $token)
    {
        $reset = PasswordReset::where('token', $token)->first();

        if (!$reset || !$reset->isValid()) {
            return redirect()->route('oxalis.password.forgot')
                ->withErrors(['email' => 'This password reset link is invalid or has expired.']);
        }

        return view('oxalis::auth.reset-password', ['token' => $token]);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token'                 => 'required',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $reset = PasswordReset::where('token', $data['token'])->first();

        if (!$reset || !$reset->isValid()) {
            return back()->withErrors(['token' => 'This link is invalid or has expired.']);
        }

        $userModel = config('oxalis.user_model');
        $user = $userModel::find($reset->user_id);

        if (!$user) {
            return back()->withErrors(['token' => 'User not found.']);
        }

        $user->update(['password' => Hash::make($data['password'])]);
        $reset->update(['used_at' => now()]);

        // Invalidate all active sessions after password reset
        try {
            OxalisSession::revokeAllForUser($user->getAuthIdentifier());
        } catch (\Throwable) {}

        return redirect()->route('oxalis.login')
            ->with('status', 'Password reset successfully. All active sessions have been signed out.');
    }

    private function sendMail(string $email, string $url): void
    {
        if (in_array(config('mail.default'), ['log', 'array', 'null'], true)) {
            return;
        }

        Mail::to($email)->send(new PasswordResetMail($url));
    }
}
