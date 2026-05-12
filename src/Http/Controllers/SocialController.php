<?php
namespace Oxalis\Http\Controllers;

use Oxalis\Auth\LoginHandler;
use Oxalis\Models\SocialLogin;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    private array $supported = ['google', 'github'];

    public function __construct(private readonly LoginHandler $login) {}

    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, $this->supported), 404);
        abort_unless(config("oxalis.social.{$provider}.enabled", false), 404);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider)
    {
        abort_unless(in_array($provider, $this->supported), 404);
        abort_unless(config("oxalis.social.{$provider}.enabled", false), 404);

        // Fetch OAuth user
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable) {
            return redirect()->route('oxalis.login')
                ->withErrors(['email' => "Could not authenticate with {$provider}. Please try again."]);
        }

        // GitHub can hide email — handle gracefully
        $email = $socialUser->getEmail();
        if (!$email) {
            return redirect()->route('oxalis.login')
                ->withErrors(['email' => "Your {$provider} account does not have a public email address. Please make your email public on {$provider} and try again."]);
        }

        // Domain allowlist check
        $allowed = array_filter(array_map('trim', explode(',', config('oxalis.allowed_domains', ''))));
        if (!empty($allowed)) {
            $domain = strtolower(substr($email, strpos($email, '@') + 1));
            if (!in_array($domain, array_map('strtolower', $allowed))) {
                return redirect()->route('oxalis.login')
                    ->withErrors(['email' => "Registration is restricted. Your email domain is not allowed."]);
            }
        }

        $userModel = config('oxalis.user_model');

        // Find by existing social link first
        $social = SocialLogin::where('provider', $provider)
            ->where('provider_id', (string) $socialUser->getId())
            ->first();

        if ($social) {
            $user = $userModel::find($social->user_id);

            // Guard: account deleted but social record remains
            if (!$user) {
                $social->delete();
                return redirect()->route('oxalis.login')
                    ->withErrors(['email' => 'This account no longer exists. Please register again.']);
            }
        } else {
            // Find or create user by email
            $user = $userModel::firstOrCreate(
                ['email' => $email],
                [
                    'name'              => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                    'password'          => Hash::make(Str::random(40)),
                    'email_verified_at' => now(),
                ],
            );

            try {
                SocialLogin::create([
                    'user_id'     => (string) $user->getAuthIdentifier(),
                    'provider'    => $provider,
                    'provider_id' => (string) $socialUser->getId(),
                    'email'       => $email,
                    'avatar'      => $socialUser->getAvatar(),
                ]);
            } catch (\Throwable) {}
        }

        // Route through LoginHandler — enforces TOTP, creates session, fires events
        return $this->login->attempt(
            user:      $user,
            method:    "social:{$provider}",
            ip:        $request->ip(),
            userAgent: $request->userAgent() ?? '',
            remember:  true,
        );
    }
}
