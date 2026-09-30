<?php
namespace Oxalis\Passkeys;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;
use Oxalis\Models\PasskeyRecoveryCode;

class PasskeyRecoveryService
{
    public function hasActiveCodes(Authenticatable $user): bool
    {
        return PasskeyRecoveryCode::where('user_id', (string) $user->getAuthIdentifier())
            ->whereNull('used_at')
            ->exists();
    }

    public function activeCount(Authenticatable $user): int
    {
        return PasskeyRecoveryCode::where('user_id', (string) $user->getAuthIdentifier())
            ->whereNull('used_at')
            ->count();
    }

    public function generate(Authenticatable $user, int $count = 8): array
    {
        $uid = (string) $user->getAuthIdentifier();

        PasskeyRecoveryCode::where('user_id', $uid)->delete();

        $plainCodes = [];

        for ($i = 0; $i < $count; $i++) {
            $code = $this->newCode();
            $plainCodes[] = $code;

            PasskeyRecoveryCode::create([
                'user_id'   => $uid,
                'code_hash' => password_hash($code, PASSWORD_DEFAULT),
            ]);
        }

        return $plainCodes;
    }

    public function ensureGenerated(Authenticatable $user, int $count = 8): ?array
    {
        if ($this->hasActiveCodes($user)) {
            return null;
        }

        return $this->generate($user, $count);
    }

    public function consume(Authenticatable $user, string $code): bool
    {
        $normalized = $this->normalize($code);

        if ($normalized === '') {
            return false;
        }

        $codes = PasskeyRecoveryCode::where('user_id', (string) $user->getAuthIdentifier())
            ->whereNull('used_at')
            ->get();

        foreach ($codes as $row) {
            if (password_verify($normalized, $row->code_hash)) {
                $row->update(['used_at' => now()]);
                return true;
            }
        }

        return false;
    }

    private function newCode(): string
    {
        return strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4));
    }

    private function normalize(string $code): string
    {
        $clean = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $code) ?? '');

        if (strlen($clean) !== 12) {
            return strtoupper(trim($code));
        }

        return substr($clean, 0, 4).'-'.substr($clean, 4, 4).'-'.substr($clean, 8, 4);
    }
}
