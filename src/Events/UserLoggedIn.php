<?php
namespace Oxalis\Events;

use Illuminate\Contracts\Auth\Authenticatable;

class UserLoggedIn
{
    public function __construct(
        public readonly Authenticatable $user,
        public readonly string $method,
        public readonly string $ip,
        public readonly string $userAgent,
    ) {}
}
