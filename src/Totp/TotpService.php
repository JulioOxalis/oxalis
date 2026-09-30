<?php
namespace Oxalis\Totp;

use Oxalis\Events\TotpEnabled;
use Oxalis\Models\TotpSecret;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TotpService
{
    private Google2FA $g2fa;

    public function __construct()
    {
        $this->g2fa = new Google2FA();
    }

    public function isEnabled(Authenticatable $user): bool
    {
        return TotpSecret::where('user_id', $user->getAuthIdentifier())
            ->whereNotNull('confirmed_at')
            ->exists();
    }

    /** Start setup: generate a secret, persist unconfirmed, return QR URI + secret. */
    public function beginSetup(Authenticatable $user): array
    {
        $secret = $this->g2fa->generateSecretKey(32);

        TotpSecret::updateOrCreate(
            ['user_id' => $user->getAuthIdentifier()],
            ['secret' => $secret, 'confirmed_at' => null],
        );

        $qrUri = $this->g2fa->getQRCodeUrl(
            company: config('app.name'),
            holder:  $user->email,
            secret:  $secret,
        );

        return ['secret' => $secret, 'qr_uri' => $qrUri];
    }

    /** Confirm setup: verify first code, mark as enabled. */
    public function confirmSetup(Authenticatable $user, string $code): bool
    {
        $row = TotpSecret::where('user_id', $user->getAuthIdentifier())
            ->whereNull('confirmed_at')
            ->first();

        if (!$row) return false;

        // verifyKeyNewer with null old-timestamp on first use — establishes the baseline.
        $ts = $this->g2fa->verifyKeyNewer($row->secret, $code, null);
        if ($ts === false) return false;

        $row->update(['confirmed_at' => now(), 'last_totp_ts' => $ts]);

        event(new TotpEnabled($user));

        return true;
    }

    /** Verify a code for an already-enabled TOTP. */
    public function verify(Authenticatable $user, string $code): bool
    {
        $row = TotpSecret::where('user_id', $user->getAuthIdentifier())
            ->whereNotNull('confirmed_at')
            ->first();

        if (!$row) return false;

        // verifyKeyNewer refuses codes whose time-step index is <= the last accepted one,
        // preventing replay of an intercepted code within the same 30-second window.
        $ts = $this->g2fa->verifyKeyNewer($row->secret, $code, $row->last_totp_ts);
        if ($ts === false) return false;

        $row->update(['last_totp_ts' => $ts]);
        return true;
    }

    /** Verify a raw secret + code (used during step-up without a user object). */
    public function verifyCode(string $secret, string $code): bool
    {
        return (bool) $this->g2fa->verifyKey($secret, $code);
    }

    public function disable(Authenticatable $user): void
    {
        TotpSecret::where('user_id', $user->getAuthIdentifier())->delete();
    }

    public function generateRecoveryCodes(Authenticatable $user): array
    {
        $plainCodes = [];
        $hashedCodes = [];

        for ($i = 0; $i < 8; $i++) {
            $code = strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
            $plainCodes[] = $code;
            $hashedCodes[] = bcrypt($code);
        }

        TotpSecret::where('user_id', $user->getAuthIdentifier())
            ->update(['recovery_codes' => json_encode($hashedCodes)]);

        return $plainCodes;
    }

    public function verifyRecoveryCode(Authenticatable $user, string $code): bool
    {
        $row = TotpSecret::where('user_id', $user->getAuthIdentifier())
            ->whereNotNull('confirmed_at')
            ->first();

        if (!$row || empty($row->recovery_codes)) {
            return false;
        }

        $codes = $row->recovery_codes;

        foreach ($codes as $index => $hash) {
            if (password_verify($code, $hash)) {
                unset($codes[$index]);
                $row->recovery_codes = array_values($codes);
                $row->save();
                return true;
            }
        }

        return false;
    }

    public function hasRecoveryCodes(Authenticatable $user): bool
    {
        return TotpSecret::where('user_id', $user->getAuthIdentifier())
            ->whereNotNull('recovery_codes')
            ->where('recovery_codes', '!=', '[]')
            ->where('recovery_codes', '!=', 'null')
            ->exists();
    }
}
