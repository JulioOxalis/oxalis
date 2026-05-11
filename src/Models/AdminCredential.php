<?php
namespace Oxalis\Models;

use Illuminate\Support\Str;

class AdminCredential extends OxalisModel
{
    protected $table = 'oxalis_admin_credentials';

    protected $fillable = [
        'password_hash', 'totp_secret', 'totp_confirmed_at',
        'session_version', 'last_login_at', 'last_login_ip',
    ];

    protected $casts = [
        'totp_confirmed_at' => 'datetime',
        'last_login_at'     => 'datetime',
    ];

    protected $hidden = ['password_hash', 'totp_secret'];

    public static function isSetup(): bool
    {
        try {
            return static::exists();
        } catch (\Throwable) {
            return false;
        }
    }

    public function hasTotpEnabled(): bool
    {
        return !empty($this->totp_confirmed_at);
    }

    public function rotateSessionVersion(): void
    {
        $this->update(['session_version' => Str::random(32)]);
    }

    public function recordLogin(string $ip): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }
}
