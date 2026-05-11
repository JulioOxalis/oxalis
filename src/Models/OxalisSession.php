<?php
namespace Oxalis\Models;

use Illuminate\Support\Str;

class OxalisSession extends OxalisModel
{
    protected $table = 'oxalis_sessions';

    protected $fillable = [
        'user_id', 'token', 'ip_address', 'user_agent',
        'device_label', 'method', 'last_active_at',
    ];

    protected $casts = ['last_active_at' => 'datetime'];

    public static function createForUser(
        mixed  $userId,
        string $ip,
        string $userAgent,
        string $method,
    ): array {
        $token = Str::random(64);

        static::create([
            'user_id'        => (string) $userId,
            'token'          => hash('sha256', $token),
            'ip_address'     => $ip,
            'user_agent'     => substr($userAgent, 0, 500),
            'device_label'   => static::labelFromAgent($userAgent),
            'method'         => $method,
            'last_active_at' => now(),
        ]);

        return $token;
    }

    public static function findByToken(string $token): ?self
    {
        return static::where('token', hash('sha256', $token))->first();
    }

    public function touch(): void
    {
        $this->update(['last_active_at' => now()]);
    }

    public static function revokeAllForUser(mixed $userId): void
    {
        static::where('user_id', (string) $userId)->delete();
    }

    private static function labelFromAgent(string $ua): string
    {
        $browser = match (true) {
            str_contains($ua, 'Edg')     => 'Edge',
            str_contains($ua, 'Chrome')  => 'Chrome',
            str_contains($ua, 'Firefox') => 'Firefox',
            str_contains($ua, 'Safari')  => 'Safari',
            str_contains($ua, 'Opera')   => 'Opera',
            default                       => 'Browser',
        };

        $os = match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Mac')     => 'macOS',
            str_contains($ua, 'iPhone')  => 'iPhone',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Linux')   => 'Linux',
            default                       => 'Unknown',
        };

        return "{$browser} on {$os}";
    }
}
