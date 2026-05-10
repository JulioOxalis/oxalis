<?php
namespace Oxalis\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool hasPasskeys(\Illuminate\Contracts\Auth\Authenticatable $user)
 * @method static array beginRegistration(\Illuminate\Contracts\Auth\Authenticatable $user, string $label = 'My Passkey')
 * @method static \Oxalis\Models\Passkey finishRegistration(\Illuminate\Contracts\Auth\Authenticatable $user, array $response)
 * @method static array beginAuthentication(?\ Illuminate\Contracts\Auth\Authenticatable $user = null)
 * @method static \Illuminate\Contracts\Auth\Authenticatable|null finishAuthentication(array $response)
 */
class Oxalis extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Oxalis\OxalisManager::class;
    }
}
