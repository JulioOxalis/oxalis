<?php
namespace Oxalis\Concerns;

use Oxalis\Models\Passkey;
use Oxalis\Models\TrustedDevice;
use Oxalis\Models\AuthEvent;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasPasskeys
{
    public function passkeys(): HasMany
    {
        return $this->hasMany(Passkey::class, 'user_id');
    }

    public function trustedDevices(): HasMany
    {
        return $this->hasMany(TrustedDevice::class, 'user_id');
    }

    public function authEvents(): HasMany
    {
        return $this->hasMany(AuthEvent::class, 'user_id');
    }

    public function hasPasskeys(): bool
    {
        return $this->passkeys()->exists();
    }

    public function getUserHandleAttribute(): string
    {
        return hash_hmac('sha256', $this->getAuthIdentifier().'|oxalis', config('app.key'));
    }
}
