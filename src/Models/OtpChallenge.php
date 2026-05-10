<?php
namespace Oxalis\Models;

use Oxalis\Models\OxalisModel;

class OtpChallenge extends OxalisModel
{
    protected $table = 'oxalis_otp_challenges';

    protected $fillable = [
        'user_id', 'token', 'code_hash', 'status',
        'attempts', 'max_attempts', 'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
