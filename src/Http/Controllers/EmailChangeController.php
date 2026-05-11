<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailChangeController extends Controller
{
    public function show()
    {
        return view('oxalis::account.email-change');
    }

    public function send(Request $request)
    {
        $userModel = config('oxalis.user_model');
        $user      = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'email'            => ['required', 'email', 'different:'.$user->email,
                'unique:'.(new $userModel)->getTable().',email'],
        ]);

        // Verify current password before allowing email change
        if (!password_verify($request->current_password, $user->password ?? '')) {
            return back()->withErrors(['current_password' => 'Incorrect password.']);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        session([
            'oxalis_email_change_new'      => $request->email,
            'oxalis_email_change_code'     => bcrypt($code),
            'oxalis_email_change_expires'  => now()->addMinutes(10)->timestamp,
        ]);

        if (app()->isLocal()) {
            session(['oxalis_email_change_dev_code' => $code]);
        }

        try {
            Mail::to($request->email)->send(new OtpMail($code, 10));
        } catch (\Throwable $e) {
            Log::warning('Oxalis email-change OTP failed', ['error' => $e->getMessage()]);
        }

        return redirect()->route('oxalis.account.email.verify.show');
    }

    public function showVerify()
    {
        if (!session('oxalis_email_change_new')) {
            return redirect()->route('oxalis.account');
        }

        $hint = app()->isLocal() ? session('oxalis_email_change_dev_code') : null;
        $newEmail = session('oxalis_email_change_new');

        return view('oxalis::account.email-change-verify', compact('hint', 'newEmail'));
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $hash    = session('oxalis_email_change_code');
        $expires = session('oxalis_email_change_expires');
        $newEmail = session('oxalis_email_change_new');

        if (!$hash || !$expires || now()->timestamp > $expires || !$newEmail) {
            session()->forget(['oxalis_email_change_new', 'oxalis_email_change_code', 'oxalis_email_change_expires', 'oxalis_email_change_dev_code']);
            return redirect()->route('oxalis.account.email.show')
                ->withErrors(['code' => 'Code expired. Please request a new one.']);
        }

        if (!password_verify($request->code, $hash)) {
            return back()->withErrors(['code' => 'Incorrect code. Try again.']);
        }

        $userModel = config('oxalis.user_model');
        $user = $userModel::find(Auth::id());
        $user->email = $newEmail;
        $user->email_verified_at = now();
        $user->save();

        session()->forget(['oxalis_email_change_new', 'oxalis_email_change_code', 'oxalis_email_change_expires', 'oxalis_email_change_dev_code']);

        return redirect()->route('oxalis.account')
            ->with('status', 'Email address updated successfully.');
    }
}
