<?php
namespace Oxalis\Models;

use Oxalis\Models\OxalisModel;

class TotpSecret extends OxalisModel
{
    protected $table = 'oxalis_totp_secrets';

    protected $fillable = ['user_id', 'secret', 'confirmed_at', 'recovery_codes'];

    protected $casts = [
        'confirmed_at'   => 'datetime',
        'secret'         => 'encrypted', // uses Laravel's built-in encryption
        'recovery_codes' => 'array',
    ];

    public function isEnabled(): bool
    {
        return $this->confirmed_at !== null;
    }
}
