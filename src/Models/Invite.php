<?php
namespace Oxalis\Models;

use Illuminate\Support\Str;

class Invite extends OxalisModel
{
    protected $table = 'oxalis_invites';

    protected $fillable = ['code', 'note', 'max_uses', 'uses', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];

    public static function generate(?string $note = null, int $maxUses = 1, ?int $expiresInDays = null): self
    {
        return static::create([
            'code'       => strtoupper(Str::random(8)),
            'note'       => $note,
            'max_uses'   => $maxUses,
            'uses'       => 0,
            'expires_at' => $expiresInDays ? now()->addDays($expiresInDays) : null,
        ]);
    }

    public function isValid(): bool
    {
        if ($this->uses >= $this->max_uses) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        return true;
    }

    public function consume(): void
    {
        $this->increment('uses');
    }
}
