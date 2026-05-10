<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Auth\LoginHandler;
use Oxalis\Events\LoginFailed;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function __construct(private readonly LoginHandler $login) {}

    public function showLogin()
    {
        return view('oxalis::auth.password-login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $userModel = config('oxalis.user_model');
        $user = $userModel::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            session(['oxalis_login_failed' => true]);

            event(new LoginFailed(
                email:    $data['email'],
                method:   'password',
                ip:       $request->ip(),
                attempts: 1,
            ));

            return back()->withErrors(['email' => 'These credentials do not match our records.'])
                         ->withInput(['email' => $data['email']]);
        }

        session(['oxalis_login_success' => true]);

        return $this->login->attempt(
            user:      $user,
            method:    'password',
            ip:        $request->ip(),
            userAgent: $request->userAgent(),
            remember:  $request->boolean('remember'),
        );
    }
}
