<?php
namespace Oxalis\Http\Controllers;

use Oxalis\EmailVerification\EmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class EmailVerificationController extends Controller
{
    public function __construct(private EmailVerificationService $service) {}

    public function notice()
    {
        $devLink = app()->isLocal() ? session('oxalis_dev_verify_link') : null;

        return view('oxalis::auth.verify-email-notice', compact('devLink'));
    }

    public function send(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $userModel = config('oxalis.user_model');
        $user = $userModel::where('email', $request->email)->first();

        if ($user) {
            $this->service->send($user);
        }

        $devLink = app()->isLocal() ? session('oxalis_dev_verify_link') : null;

        return view('oxalis::auth.verify-email-sent', [
            'email'   => $request->email,
            'devLink' => $devLink,
        ]);
    }

    public function verify(Request $request, string $token)
    {
        if (!$this->service->verify($token)) {
            return redirect()->route('oxalis.login')
                ->withErrors(['email' => 'This verification link is invalid or expired.']);
        }

        return redirect(config('oxalis.routes.home', '/dashboard'))
            ->with('status', 'Email verified!');
    }
}
