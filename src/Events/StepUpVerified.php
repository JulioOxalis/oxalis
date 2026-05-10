<?php
namespace Oxalis\Events;

use Illuminate\Contracts\Auth\Authenticatable;

class StepUpVerified
{
    public function __construct(
        public readonly Authenticatable $user,
        public readonly string $method,
    ) {}
}
