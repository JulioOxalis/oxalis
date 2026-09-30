<?php
namespace Oxalis\Events;

class LoginFailed
{
    public function __construct(
        public readonly string $email,
        public readonly string $method,
        public readonly string $ip,
        public readonly int $attempts,
    ) {}
}
