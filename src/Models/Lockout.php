<?php
namespace Oxalis\Models;

use Oxalis\Models\OxalisModel;

class Lockout extends OxalisModel
{
    protected $table = 'oxalis_lockouts';

    protected $fillable = ['key', 'attempts', 'locked_until'];

    protected $casts = ['locked_until' => 'datetime'];

    public static function keyFor(string $email, string $ip): string
    {
        return hash('sha256', strtolower(trim($email)).'|'.$ip);
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function secondsRemaining(): int
    {
        return $this->isLocked() ? (int) now()->diffInSeconds($this->locked_until) : 0;
    }
}
