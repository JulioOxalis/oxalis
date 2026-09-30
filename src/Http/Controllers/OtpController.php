<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Auth\LoginHandler;
use Oxalis\EmailOtp\OtpService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OtpController extends Controller
{
    public function __construct(
        private readonly OtpService   $otp,
        private readonly LoginHandler $login,
    ) {}

    public function send(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $userModel = config('oxalis.user_model');
        $user = $userModel::where('email', $request->email)->first();

        $hint = null;

        if ($user) {
            $challenge = $this->otp->send($user);

            session([
                'oxalis_otp_token'   => $challenge->token,
                'oxalis_otp_user_id' => $user->getAuthIdentifier(),
                'oxalis_remember'    => $request->boolean('remember'),
            ]);

            $hint = app()->isLocal() ? session('oxalis_dev_otp') : null;
        }

        return view('oxalis::auth.otp', compact('hint'));
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $token = session('oxalis_otp_token');

        if (!$token || !$this->otp->verify($token, $request->code, $request->ip())) {
            return back()->withErrors(['code' => 'Invalid or expired code.']);
        }

        $user = $this->otp->userFromToken($token);

        if (!$user) {
            return back()->withErrors(['code' => 'Something went wrong. Please try again.']);
        }

        $remember = (bool) session('oxalis_remember', false);

        session()->forget(['oxalis_otp_token', 'oxalis_otp_user_id', 'oxalis_dev_otp', 'oxalis_remember']);

        return $this->login->attempt(
            user:      $user,
            method:    'email_otp',
            ip:        $request->ip(),
            userAgent: $request->userAgent(),
            remember:  $remember,
        );
    }
}
