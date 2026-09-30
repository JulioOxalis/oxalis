<?php
namespace Oxalis\Models;

use Oxalis\Models\OxalisModel;

class TrustedDevice extends OxalisModel
{
    protected $table = 'oxalis_trusted_devices';

    protected $fillable = [
        'user_id', 'fingerprint', 'label', 'last_seen_at', 'expires_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];
}
