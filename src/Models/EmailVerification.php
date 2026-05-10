<?php
namespace Oxalis\Models;

use Oxalis\Models\OxalisModel;

class EmailVerification extends OxalisModel
{
    protected $table = 'oxalis_email_verifications';

    protected $fillable = ['user_id', 'token', 'expires_at', 'verified_at'];

    protected $casts = [
        'expires_at'  => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function isValid(): bool
    {
        return $this->verified_at === null && $this->expires_at->isFuture();
    }
}
