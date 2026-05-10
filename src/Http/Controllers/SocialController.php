<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Models\AuthEvent;
use Oxalis\Models\SocialLogin;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    private array $supported = ['google', 'github'];

    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, $this->supported), 404);
        abort_unless(config("oxalis.social.{$provider}.enabled", false), 404);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider)
    {
        abort_unless(in_array($provider, $this->supported), 404);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable) {
            return redirect()->route('oxalis.login')
                ->withErrors(['email' => "Could not authenticate with {$provider}. Please try again."]);
        }

        // Find existing social login
        $social = SocialLogin::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($social) {
            $userModel = config('oxalis.user_model');
            $user = $userModel::find($social->user_id);
        } else {
            // Find or create user by email
            $userModel = config('oxalis.user_model');
            $user = $userModel::firstOrCreate(
                ['email' => $socialUser->getEmail()],
                ['name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User'],
            );

            SocialLogin::create([
                'user_id'     => $user->getAuthIdentifier(),
                'provider'    => $provider,
                'provider_id' => $socialUser->getId(),
                'email'       => $socialUser->getEmail(),
                'avatar'      => $socialUser->getAvatar(),
            ]);
        }

        Auth::login($user, true);

        AuthEvent::create([
            'user_id'    => $user->getAuthIdentifier(),
            'event'      => 'login',
            'method'     => "social:{$provider}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status'     => 'success',
        ]);

        return redirect(config('oxalis.routes.home', '/dashboard'));
    }
}
