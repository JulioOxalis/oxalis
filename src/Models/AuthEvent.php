<?php
namespace Oxalis\Models;

use Oxalis\Models\OxalisModel;

class AuthEvent extends OxalisModel
{
    protected $table = 'oxalis_auth_events';
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'event', 'method', 'ip_address', 'user_agent', 'status',
    ];
}
