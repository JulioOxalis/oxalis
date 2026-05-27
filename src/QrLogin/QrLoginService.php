<?php
namespace Oxalis\QrLogin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class QrLoginService
{
    private const PREFIX = 'ox_qr_';

    public function generateToken(): string
    {
        $token = Str::random(32);
        Cache::put(
            self::PREFIX . $token,
            ['status' => 'pending', 'user_id' => null],
            config('oxalis.qr_login.ttl', 90),
        );
        return $token;
    }

    public function check(string $token): array
    {
        return Cache::get(self::PREFIX . $token) ?? ['status' => 'expired'];
    }

    public function approve(string $token, int|string $userId): bool
    {
        $key  = self::PREFIX . $token;
        $data = Cache::get($key);

        if (!$data || $data['status'] !== 'pending') {
            return false;
        }

        Cache::put($key, ['status' => 'approved', 'user_id' => $userId],
            config('oxalis.qr_login.ttl', 90));

        return true;
    }

    /** Returns the user_id and removes the token (single-use). */
    public function consume(string $token): int|string|null
    {
        $key  = self::PREFIX . $token;
        $data = Cache::get($key);

        if (!$data || $data['status'] !== 'approved') {
            return null;
        }

        Cache::forget($key);
        return $data['user_id'];
    }
}
