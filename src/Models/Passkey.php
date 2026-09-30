<?php
namespace Oxalis\Models;

use Oxalis\Models\OxalisModel;

class Passkey extends OxalisModel
{
    protected $table = 'oxalis_passkeys';

    protected $fillable = [
        'user_id',
        'label',
        'credential_id',
        'public_key_json',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];
}
