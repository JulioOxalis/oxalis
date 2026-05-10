<?php
namespace Oxalis\Events;

use Illuminate\Contracts\Auth\Authenticatable;

class TotpEnabled
{
    public function __construct(public readonly Authenticatable $user) {}
}
