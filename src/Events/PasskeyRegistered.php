<?php
namespace Oxalis\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Oxalis\Models\Passkey;

class PasskeyRegistered
{
    public function __construct(
        public readonly Authenticatable $user,
        public readonly Passkey $passkey,
    ) {}
}
