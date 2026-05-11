<?php
namespace Oxalis\Models;

use Illuminate\Support\Str;

class Webhook extends OxalisModel
{
    protected $table = 'oxalis_webhooks';

    protected $fillable = ['url', 'secret', 'events', 'note', 'active', 'last_fired_at', 'failures'];

    protected $casts = [
        'events'        => 'array',
        'active'        => 'boolean',
        'last_fired_at' => 'datetime',
    ];

    protected $hidden = ['secret'];

    public static function generateSecret(): string
    {
        return Str::random(40);
    }

    public function sign(string $payload): string
    {
        return 'sha256=' . hash_hmac('sha256', $payload, $this->secret);
    }

    public function listensTo(string $event): bool
    {
        return in_array('*', $this->events ?? []) || in_array($event, $this->events ?? []);
    }
}
