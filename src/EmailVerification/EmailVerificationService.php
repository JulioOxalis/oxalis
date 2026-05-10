<?php
namespace Oxalis\EmailVerification;

use Oxalis\Mail\EmailVerificationMail;
use Oxalis\Models\EmailVerification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailVerificationService
{
    public function send(Authenticatable $user): void
    {
        // Expire old unverified tokens for this user
        EmailVerification::where('user_id', $user->getAuthIdentifier())
            ->whereNull('verified_at')
            ->delete();

        // Generate token and store
        $token = Str::random(64);

        EmailVerification::create([
            'user_id'    => $user->getAuthIdentifier(),
            'token'      => $token,
            'expires_at' => now()->addHours(24),
        ]);

        $url = route('oxalis.email.verify', ['token' => $token]);

        // Store dev link in session when running locally
        if (app()->isLocal()) {
            session(['oxalis_dev_verify_link' => $url]);
        }

        // Send email unless mail driver is a non-sending driver
        if (!in_array(config('mail.default'), ['log', 'array', 'null'])) {
            Mail::to($user->email)->send(new EmailVerificationMail($url));
        }
    }

    public function verify(string $token): bool
    {
        $verification = EmailVerification::where('token', $token)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return false;
        }

        $verification->update(['verified_at' => now()]);

        return true;
    }

    public function isVerified(Authenticatable $user): bool
    {
        return EmailVerification::where('user_id', $user->getAuthIdentifier())
            ->whereNotNull('verified_at')
            ->exists();
    }
}
