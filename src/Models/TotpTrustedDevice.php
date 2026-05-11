<?php
namespace Oxalis\Models;

class TotpTrustedDevice extends OxalisModel
{
    protected $table = 'oxalis_totp_trusted_devices';

    protected $fillable = ['user_id', 'token', 'ip_address', 'user_agent', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];

    public function isValid(): bool
    {
        return $this->expires_at->isFuture();
    }

    public static function findByToken(string $token): ?self
    {
        return static::where('token', hash('sha256', $token))
            ->where('expires_at', '>', now())
            ->first();
    }

    public static function createForUser(
        mixed  $userId,
        string $token,
        string $ip,
        string $userAgent,
    ): self {
        return static::create([
            'user_id'    => $userId,
            'token'      => hash('sha256', $token),
            'ip_address' => $ip,
            'user_agent' => substr($userAgent, 0, 200),
            'expires_at' => now()->addDays(config('oxalis.totp_trust.days', 30)),
        ]);
    }
}
