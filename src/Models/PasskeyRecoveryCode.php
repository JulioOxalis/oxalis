<?php
namespace Oxalis\Models;

class PasskeyRecoveryCode extends OxalisModel
{
    protected $table = 'oxalis_passkey_recovery_codes';

    protected $fillable = ['user_id', 'code_hash', 'used_at'];

    protected $casts = [
        'used_at' => 'datetime',
    ];
}
