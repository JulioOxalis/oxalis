<?php
namespace Oxalis\Models;

use Oxalis\Models\OxalisModel;

class MagicLink extends OxalisModel
{
    protected $table = 'oxalis_magic_links';

    protected $fillable = [
        'user_id', 'token', 'expires_at', 'used_at', 'ip_address',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }
}
