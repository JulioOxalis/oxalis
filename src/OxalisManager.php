<?php
namespace Oxalis;

use Illuminate\Contracts\Auth\Authenticatable;
use Oxalis\EmailOtp\OtpService;
use Oxalis\MagicLink\MagicLinkService;
use Oxalis\Models\Passkey;
use Oxalis\StepUp\StepUpService;
use Oxalis\Totp\TotpService;
use Oxalis\WebAuthn\WebAuthnService;

class OxalisManager
{
    public function __construct(
        private readonly WebAuthnService  $webAuthn,
        private readonly OtpService       $otp,
        private readonly MagicLinkService $magicLink,
        private readonly TotpService      $totp,
        private readonly StepUpService    $stepUp,
    ) {}

    // ── Passkeys ──────────────────────────────────────────────────────────────

    public function hasPasskeys(Authenticatable $user): bool
    {
        return $this->webAuthn->hasPasskeys($user);
    }

    public function beginRegistration(Authenticatable $user, string $label = 'My Passkey'): array
    {
        return $this->webAuthn->beginRegistration($user, $label);
    }

    public function finishRegistration(Authenticatable $user, array $response, ?string $host = null): Passkey
    {
        return $this->webAuthn->finishRegistration($user, $response, $host);
    }

    public function beginAuthentication(?Authenticatable $user = null): array
    {
        return $this->webAuthn->beginAuthentication($user);
    }

    /**
     * @return array{user: Authenticatable, passkey: Passkey}
     */
    public function finishAuthentication(array $response, ?string $host = null): array
    {
        return $this->webAuthn->finishAuthentication($response, $host);
    }

    // ── Service accessors ─────────────────────────────────────────────────────

    public function webAuthn(): WebAuthnService    { return $this->webAuthn; }
    public function otp(): OtpService              { return $this->otp; }
    public function magicLink(): MagicLinkService  { return $this->magicLink; }
    public function totp(): TotpService            { return $this->totp; }
    public function stepUp(): StepUpService        { return $this->stepUp; }
}
