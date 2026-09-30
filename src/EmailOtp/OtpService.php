<?php
namespace Oxalis\EmailOtp;

use Oxalis\Mail\OtpMail;
use Oxalis\Models\Lockout;
use Oxalis\Models\OtpChallenge;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OtpService
{
    public function send(Authenticatable $user): OtpChallenge
    {
        OtpChallenge::where('user_id', $user->getAuthIdentifier())
            ->where('status', 'pending')
            ->update(['status' => 'expired']);

        $length = config('oxalis.otp.length', 6);
        $code = str_pad((string) random_int(0, (int) str_repeat('9', $length)), $length, '0', STR_PAD_LEFT);

        $challenge = OtpChallenge::create([
            'user_id'      => $user->getAuthIdentifier(),
            'token'        => Str::random(40),
            'code_hash'    => bcrypt($code),
            'status'       => 'pending',
            'attempts'     => 0,
            'max_attempts' => config('oxalis.otp.max_attempts', 5),
            'expires_at'   => now()->addMinutes(config('oxalis.otp.expires_in', 5)),
        ]);

        if (app()->isLocal()) {
            session(['oxalis_dev_otp' => $code]);
        }

        $this->sendMail($user->email, $code);

        return $challenge;
    }

    /**
     * Verify a code. Tracks failures against both the challenge (per-user)
     * and the IP address (prevents distributed brute-force across many accounts).
     */
    public function verify(string $token, string $code, ?string $ip = null): bool
    {
        $challenge = OtpChallenge::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if (!$challenge) {
            return false;
        }

        // IP-based lockout — shared across all OTP challenges from the same IP
        if ($ip) {
            $ipKey   = 'otp|' . hash('sha256', $ip);
            $lockout = Lockout::firstOrCreate(['key' => $ipKey], ['attempts' => 0]);

            if ($lockout->isLocked()) {
                return false;
            }
        }

        $challenge->increment('attempts');

        if ($challenge->attempts >= $challenge->max_attempts) {
            $challenge->update(['status' => 'locked']);
            if (isset($lockout)) {
                $this->incrementIpLockout($lockout);
            }
            return false;
        }

        if (!password_verify($code, $challenge->code_hash)) {
            if (isset($lockout)) {
                $this->incrementIpLockout($lockout);
            }
            return false;
        }

        // Success — reset IP lockout counter
        if (isset($lockout)) {
            $lockout->update(['attempts' => 0, 'locked_until' => null]);
        }

        $challenge->update(['status' => 'approved']);
        return true;
    }

    public function userFromToken(string $token): ?Authenticatable
    {
        $challenge = OtpChallenge::where('token', $token)
            ->where('status', 'approved')
            ->first();

        if (!$challenge) return null;

        $userModel = config('oxalis.user_model');
        return $userModel::find($challenge->user_id);
    }

    private function incrementIpLockout(Lockout $lockout): void
    {
        $max     = config('oxalis.lockout.max_attempts', 5) * 3; // IP threshold is 3× user threshold
        $minutes = config('oxalis.lockout.minutes', 15);

        $attempts = $lockout->attempts + 1;
        $lockedUntil = $attempts >= $max ? now()->addMinutes($minutes) : null;
        $lockout->update(['attempts' => $attempts, 'locked_until' => $lockedUntil]);
    }

    private function sendMail(string $email, string $code): void
    {
        if (in_array(config('mail.default'), ['log', 'array', 'null'], true)) {
            return;
        }

        try {
            Mail::to($email)->send(new OtpMail($code, config('oxalis.otp.expires_in', 5)));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Oxalis OTP mail failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
