<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Auth\LoginHandler;
use Oxalis\MagicLink\MagicLinkService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MagicLinkController extends Controller
{
    public function __construct(
        private readonly MagicLinkService $service,
        private readonly LoginHandler     $login,
    ) {}

    public function send(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $userModel = config('oxalis.user_model');
        $user = $userModel::where('email', $request->email)->first();

        if ($user) {
            $this->service->send($user, $request->ip());
        }

        $devLink = app()->isLocal() ? session('oxalis_dev_magic_link') : null;

        return view('oxalis::auth.magic-link-sent', [
            'email'   => $request->email,
            'devLink' => $devLink,
        ]);
    }

    public function verify(Request $request, string $token)
    {
        $user = $this->service->verify($token);

        if (!$user) {
            return redirect()->route('oxalis.login')
                ->withErrors(['email' => 'This link is invalid or has expired.']);
        }

        return $this->login->attempt(
            user:      $user,
            method:    'magic_link',
            ip:        $request->ip(),
            userAgent: $request->userAgent(),
            remember:  true,
        );
    }
}
