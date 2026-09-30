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
            'token'      => hash('sha256', $token),
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
        $hashed = hash('sha256', $token);
        $link = MagicLink::where('token', $hashed)->first();

        // Backwards compatibility for links created before Oxalis hashed tokens.
        // When one is used, upgrade the row before marking it consumed.
        if (! $link) {
            $link = MagicLink::where('token', $token)->first();
            if ($link) {
                $link->update(['token' => $hashed]);
            }
        }

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
