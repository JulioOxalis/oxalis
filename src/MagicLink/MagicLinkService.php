<?php
namespace Oxalis\MagicLink;

use Oxalis\Mail\MagicLinkMail;
use Oxalis\Models\MagicLink;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MagicLinkService
{
    public function send(Authenticatable $user, string $ip = null): void
    {
        // Expire any unused links for this user
        MagicLink::where('user_id', $user->getAuthIdentifier())
            ->whereNull('used_at')
            ->update(['expires_at' => now()]);

        $token = Str::random(64);

        MagicLink::create([
            'user_id'    => $user->getAuthIdentifier(),
            'token'      => $token,
            'expires_at' => now()->addMinutes(config('oxalis.magic_link.expires_in', 15)),
            'ip_address' => $ip,
        ]);

        $url = route('oxalis.magic-link.verify', ['token' => $token]);

        // Always show link on-screen in local dev
        if (app()->isLocal()) {
            session(['oxalis_dev_magic_link' => $url]);
        }

        $this->sendMail($user->email, $url);
    }

    public function verify(string $token): ?Authenticatable
    {
        $link = MagicLink::where('token', $token)->first();

        if (!$link || !$link->isValid()) {
            return null;
        }

        $link->update(['used_at' => now()]);

        $userModel = config('oxalis.user_model');
        return $userModel::find($link->user_id);
    }

    private function sendMail(string $email, string $url): void
    {
        if (in_array(config('mail.default'), ['log', 'array', 'null'], true)) {
            return;
        }

        try {
            Mail::to($email)->send(new MagicLinkMail($url, config('oxalis.magic_link.expires_in', 15)));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Oxalis magic-link mail failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
