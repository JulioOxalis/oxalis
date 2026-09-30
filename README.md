# Oxalis

Oxalis is a customizable Laravel authentication package built on Laravel Fortify and Laravel Passkeys.

It provides the application-facing layer around Fortify: publishable auth views, styling, branding config, security headers, security-store migrations, and clear feature toggles. Oxalis does not replace Fortify's authentication pipeline and does not register a second competing set of `/login` or `/register` routes.

## Compatibility

- PHP 8.2+
- Laravel 12 or 13
- Laravel Fortify 1.40+
- Laravel Passkeys 0.2+

## What ships in Oxalis v2

Supported core features:

- password login
- registration
- password reset
- email verification
- TOTP two-factor authentication
- WebAuthn passkeys
- app-owned Blade views and components
- fallback CSS for Packagist installs
- security headers middleware with CSP
- SQL security-store migrations for passkeys, sessions, cache, and queue tables
- Mongo-compatible guidance for storing auth/security records on SQL while users remain in MongoDB

Not shipped as default auth methods:

- magic links
- social login
- Smart Dispatch
- QR approval/login
- Bluetooth, ultrasonic, or pairing flows
- panic flows
- risk scoring
- webhooks
- package admin dashboard

Those can be built as separate modules later. Keeping them outside the default runtime prevents disabled methods from remaining reachable through old package endpoints.

## Installation

Install the package:

```bash
composer require julio/oxalis
```

Publish the Oxalis config:

```bash
php artisan vendor:publish --tag=oxalis-config
```

Publish the SQL security migrations:

```bash
php artisan vendor:publish --tag=oxalis-migrations
```

Publish the default CSS:

```bash
php artisan vendor:publish --tag=oxalis-assets
```

Publish the default auth views if you want Oxalis' starter pages:

```bash
php artisan vendor:publish --tag=oxalis-views
```

If the app does not already have Fortify config and support actions, publish Fortify's config/support files too:

```bash
php artisan vendor:publish --tag=fortify-config
php artisan vendor:publish --tag=fortify-support
```

Register your app's Fortify service provider if it is not already registered. In modern Laravel apps this is usually `bootstrap/providers.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
];
```

If you use Fortify TOTP with a SQL `users` table, publish Fortify's migrations as needed:

```bash
php artisan vendor:publish --tag=fortify-migrations
```

Run migrations:

```bash
php artisan migrate
```

Clear cached config and routes after changing auth configuration:

```bash
php artisan optimize:clear
```

## Minimal environment

For a normal SQL-backed Laravel app:

```env
APP_URL=https://example.com

OXALIS_REGISTRATION=true
OXALIS_PASSWORD_RESET=true
OXALIS_EMAIL_VERIFICATION=true
OXALIS_TOTP=true
OXALIS_PASSKEYS=true

PASSKEYS_RELYING_PARTY_ID=example.com
PASSKEYS_ALLOWED_ORIGINS=https://example.com
```

For a Mongo-backed app, keep users in MongoDB but store security/auth records on SQL:

```env
DB_CONNECTION=mongodb

OXALIS_SECURITY_CONNECTION=security
SESSION_DRIVER=database
SESSION_CONNECTION=security
CACHE_STORE=database
DB_CACHE_CONNECTION=security
DB_CACHE_LOCK_CONNECTION=security
QUEUE_CONNECTION=database
DB_QUEUE_CONNECTION=security
```

Your app must define the `security` connection in `config/database.php`. SQLite is fine for local development; production should use a persistent SQL database such as MySQL, MariaDB, PostgreSQL, or a durable SQLite file.

## Configure Fortify

Oxalis v2 intentionally uses Fortify as the canonical auth router. Configure `config/fortify.php` like this:

```php
<?php

use Laravel\Fortify\Features;

return [
    'guard' => 'web',
    'middleware' => ['web', 'oxalis.security.headers'],
    'auth_middleware' => 'auth',
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'views' => true,
    'home' => '/dashboard',
    'prefix' => '',
    'domain' => null,
    'lowercase_usernames' => true,

    'limiters' => [
        'login' => 'login',
        'two-factor' => 'two-factor',
        'passkeys' => 'passkeys',
    ],

    'passkeys' => [
        'relying_party_id' => env('PASSKEYS_RELYING_PARTY_ID', parse_url(config('app.url'), PHP_URL_HOST)),
        'allowed_origins' => array_values(array_filter(array_map(
            'trim',
            explode(',', env('PASSKEYS_ALLOWED_ORIGINS', config('app.url')))
        ))),
        'user_handle_secret' => env('PASSKEYS_USER_HANDLE_SECRET', config('app.key')),
        'timeout' => (int) env('PASSKEYS_TIMEOUT', 60000),
    ],

    'features' => array_values(array_filter([
        env('OXALIS_REGISTRATION', true) ? Features::registration() : null,
        env('OXALIS_PASSWORD_RESET', true) ? Features::resetPasswords() : null,
        env('OXALIS_EMAIL_VERIFICATION', true) ? Features::emailVerification() : null,
        env('OXALIS_TOTP', true) ? Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]) : null,
        env('OXALIS_PASSKEYS', true) ? Features::passkeys([
            'confirmPassword' => true,
        ]) : null,
    ])),
];
```

Fortify 1.40+ owns the passkey routes. Do not also register the standalone `laravel/passkeys` routes manually.

## Configure the Fortify service provider

In `App\Providers\FortifyServiceProvider`, wire Fortify to the Oxalis views and configure rate limits:

```php
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Models\Passkey;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Passkeys\Passkeys;

public function boot(): void
{
    Fortify::createUsersUsing(CreateNewUser::class);
    Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

    Fortify::loginView(fn () => view(config('oxalis.views.login')));
    Fortify::registerView(fn () => view(config('oxalis.views.register')));
    Fortify::requestPasswordResetLinkView(fn () => view(config('oxalis.views.forgot_password')));
    Fortify::resetPasswordView(fn (Request $request) => view(config('oxalis.views.reset_password'), [
        'request' => $request,
    ]));
    Fortify::verifyEmailView(fn () => view(config('oxalis.views.verify_email')));
    Fortify::twoFactorChallengeView(fn () => view(config('oxalis.views.two_factor_challenge')));
    Fortify::confirmPasswordView(fn () => view(config('oxalis.views.confirm_password')));

    Passkeys::useUserModel(User::class);
    Passkeys::usePasskeyModel(Passkey::class);

    RateLimiter::for('login', function (Request $request) {
        $key = Str::transliterate(Str::lower((string) $request->input(Fortify::username())).'|'.$request->ip());

        return Limit::perMinute(config('oxalis.security.login_attempts_per_minute'))->by($key);
    });

    RateLimiter::for('two-factor', fn (Request $request) =>
        Limit::perMinute(config('oxalis.security.two_factor_attempts_per_minute'))
            ->by((string) $request->session()->get('login.id', $request->ip()))
    );

    RateLimiter::for('passkeys', fn (Request $request) =>
        Limit::perMinute(config('oxalis.security.passkey_attempts_per_minute'))
            ->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip()))
    );
}
```

## Configure your user and passkey models

For TOTP, your user model should use Fortify's two-factor trait:

```php
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use TwoFactorAuthenticatable;
}
```

For passkeys, your user model should implement Laravel Passkeys' contract and trait:

```php
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\PasskeyAuthenticatable;

class User extends Authenticatable implements PasskeyUser
{
    use PasskeyAuthenticatable;
}
```

If your default database is MongoDB, create an app-owned passkey model that stores passkeys on SQL:

```php
namespace App\Models;

use Laravel\Passkeys\Passkey as BasePasskey;

class Passkey extends BasePasskey
{
    protected $connection = 'security';
    protected $table = 'passkeys';
}
```

Then call `Passkeys::usePasskeyModel(Passkey::class)` in your Fortify service provider.

## Feature guide

### Password login

Password login is handled by Fortify's `/login` and `/logout` routes. Oxalis provides the default login view and styles, but Fortify performs credential validation, throttling, session regeneration, and redirects.

Relevant routes:

- `GET /login`
- `POST /login`
- `POST /logout`

Useful config:

```env
OXALIS_LOGIN_RATE_LIMIT=5
```

### Registration

Enable:

```env
OXALIS_REGISTRATION=true
```

Registration is handled by Fortify's registration feature and your `CreateNewUser` action.

Relevant routes:

- `GET /register`
- `POST /register`

Use your `CreateNewUser` action for app-specific rules such as password policy, allowed domains, invite checks, team assignment, or profile defaults.

Disable public registration:

```env
OXALIS_REGISTRATION=false
```

### Password reset

Enable:

```env
OXALIS_PASSWORD_RESET=true
```

Relevant routes:

- `GET /forgot-password`
- `POST /forgot-password`
- `GET /reset-password/{token}`
- `POST /reset-password`

Make sure mail is configured in `.env`, because Fortify sends password reset notifications through Laravel's notification/mail system.

### Email verification

Enable:

```env
OXALIS_EMAIL_VERIFICATION=true
```

Your user model should implement Laravel's email verification contract:

```php
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    //
}
```

Protect verified-only routes with Laravel's `verified` middleware:

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class);
});
```

Relevant routes:

- `GET /email/verify`
- `GET /email/verify/{id}/{hash}`
- `POST /email/verification-notification`

### TOTP two-factor authentication

Enable:

```env
OXALIS_TOTP=true
OXALIS_TOTP_RATE_LIMIT=5
```

Fortify manages TOTP setup, confirmation, recovery codes, challenge screens, and disabling. Oxalis provides the challenge view and feature flag.

Relevant Fortify routes include:

- `POST /user/two-factor-authentication`
- `POST /user/confirmed-two-factor-authentication`
- `DELETE /user/two-factor-authentication`
- `GET /user/two-factor-qr-code`
- `GET /user/two-factor-recovery-codes`
- `POST /two-factor-challenge`

If you use a SQL `users` table, publish and run Fortify's migrations so the two-factor columns exist:

```bash
php artisan vendor:publish --tag=fortify-migrations
php artisan migrate
```

If you use MongoDB users, ensure your user document can store:

- `two_factor_secret`
- `two_factor_recovery_codes`
- `two_factor_confirmed_at`

### WebAuthn passkeys

Enable:

```env
OXALIS_PASSKEYS=true
OXALIS_PASSKEY_RATE_LIMIT=10
PASSKEYS_RELYING_PARTY_ID=example.com
PASSKEYS_ALLOWED_ORIGINS=https://example.com
```

Fortify 1.40+ registers the passkey routes. Oxalis supplies the SQL security migrations, default views, and config guidance.

Relevant routes:

- `GET /passkeys/login/options`
- `POST /passkeys/login`
- `GET /passkeys/confirm/options`
- `POST /passkeys/confirm`
- `GET /user/passkeys/options`
- `POST /user/passkeys`
- `DELETE /user/passkeys/{passkey}`

Important browser rules:

- production passkeys require HTTPS
- local development should use `localhost`; `127.0.0.1` and `localhost` are different WebAuthn relying-party IDs
- `PASSKEYS_RELYING_PARTY_ID` should be the registrable host, for example `example.com`
- `PASSKEYS_ALLOWED_ORIGINS` should be exact HTTPS origins, for example `https://app.example.com`

### Auth views, styling, and branding

Publish starter views:

```bash
php artisan vendor:publish --tag=oxalis-views
```

Publish fallback CSS:

```bash
php artisan vendor:publish --tag=oxalis-assets
```

Default view keys:

- `login`: `auth.login`
- `register`: `auth.register`
- `forgot_password`: `auth.forgot-password`
- `reset_password`: `auth.reset-password`
- `verify_email`: `auth.verify-email`
- `two_factor_challenge`: `auth.two-factor-challenge`
- `confirm_password`: `auth.confirm-password`

Override any view in `config/oxalis.php`:

```php
'views' => [
    'login' => 'auth.login',
    'register' => 'auth.register',
    // ...
],
```

Branding config:

```env
OXALIS_BRAND_NAME="Acme"
OXALIS_BRAND_TAGLINE="Secure access to your workspace"
```

### Security headers

Oxalis registers the middleware alias:

```text
oxalis.security.headers
```

Use it in `config/fortify.php`:

```php
'middleware' => ['web', 'oxalis.security.headers'],
```

The middleware emits:

- `Content-Security-Policy`
- `X-Content-Type-Options`
- `Referrer-Policy`
- `Permissions-Policy`
- production HTTPS `Strict-Transport-Security`

Local development CSP allows Vite on `localhost`, `127.0.0.1`, and `[::1]`.

### SQL security store

Oxalis migrations create only the tables needed by the supported v2 runtime:

- `passkeys`
- `sessions`
- `cache`
- `cache_locks`
- `jobs`
- `job_batches`
- `failed_jobs`

They are guarded so existing tables are not recreated. The target connection is:

```env
OXALIS_SECURITY_CONNECTION=security
```

If that variable is missing, Oxalis falls back to `SESSION_CONNECTION`, then `DB_CONNECTION`.

## Safe upgrade from older Oxalis/AuthX builds

Oxalis v2 removes the old package-owned route stack and the experimental custom auth methods. This is intentional and security-related.

Before upgrading an existing application:

1. Back up the database.
2. Commit your current app code.
3. Remove old package route references such as `/oxalis/*` if your app still links to them.
4. Configure Fortify as the canonical auth router.
5. Publish Oxalis views/assets only if you want the starter UI.
6. Run `php artisan optimize:clear`.
7. Run your auth test suite before deploying.

Old tables such as `authx_*`, `oxalis_magic_links`, `oxalis_social_logins`, `oxalis_invites`, `oxalis_webhooks`, and `oxalis_admin_credentials` are no longer used by Oxalis v2. The package does not drop them automatically.

Do not use `vendor:publish --force` on existing apps unless you intentionally want to overwrite local auth views.

## Configuration reference

`config/oxalis.php`:

```php
return [
    'features' => [
        'registration' => env('OXALIS_REGISTRATION', true),
        'password_reset' => env('OXALIS_PASSWORD_RESET', true),
        'email_verification' => env('OXALIS_EMAIL_VERIFICATION', true),
        'totp' => env('OXALIS_TOTP', true),
        'passkeys' => env('OXALIS_PASSKEYS', true),
    ],

    'brand' => [
        'name' => env('OXALIS_BRAND_NAME', env('APP_NAME', 'Oxalis')),
        'tagline' => env('OXALIS_BRAND_TAGLINE', 'Secure access to your account'),
    ],

    'database' => [
        'security_connection' => env('OXALIS_SECURITY_CONNECTION', env('SESSION_CONNECTION', env('DB_CONNECTION', 'mysql'))),
    ],

    'security' => [
        'password_confirmation_timeout' => (int) env('OXALIS_CONFIRM_TIMEOUT', 10800),
        'login_attempts_per_minute' => (int) env('OXALIS_LOGIN_RATE_LIMIT', 5),
        'two_factor_attempts_per_minute' => (int) env('OXALIS_TOTP_RATE_LIMIT', 5),
        'passkey_attempts_per_minute' => (int) env('OXALIS_PASSKEY_RATE_LIMIT', 10),
    ],
];
```

## Production checklist

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain.example`
- `SESSION_SECURE_COOKIE=true`
- `SESSION_HTTP_ONLY=true`
- `SESSION_SAME_SITE=lax`
- trusted proxies configured only for infrastructure you control
- `PASSKEYS_RELYING_PARTY_ID` set to the real host
- `PASSKEYS_ALLOWED_ORIGINS` set to exact HTTPS origins
- persistent SQL database configured for `OXALIS_SECURITY_CONNECTION`
- queue/cache/session tables stored on SQL when the default DB is MongoDB
- `php artisan config:cache` and `php artisan route:cache` only after confirming config is correct

## Troubleshooting

### Passkey button does nothing

Check the browser console and confirm the app is served from an allowed origin. For local testing, prefer:

```text
http://localhost:8000
```

Do not mix `localhost` and `127.0.0.1` for the same passkey ceremony.

### Passkey routes return 404

Confirm `Features::passkeys()` is present in `config/fortify.php` and that Fortify is not ignoring routes.

Then run:

```bash
php artisan optimize:clear
php artisan route:list | grep passkey
```

On Windows PowerShell:

```powershell
php artisan route:list | Select-String passkey
```

### Auth pages are unstyled

Publish the Oxalis CSS:

```bash
php artisan vendor:publish --tag=oxalis-assets
```

If Vite is configured and pages still look unstyled, remove a stale `public/hot` file or start the Vite dev server it points to.

### MongoDB says insert-or-ignore is unsupported

Do not store sessions, cache, throttling, queues, or passkeys in MongoDB. Set:

```env
SESSION_CONNECTION=security
DB_CACHE_CONNECTION=security
DB_CACHE_LOCK_CONNECTION=security
DB_QUEUE_CONNECTION=security
OXALIS_SECURITY_CONNECTION=security
```

Then run migrations on that SQL connection.

### Email verification links fail

Ensure the database cache store is using SQL, not MongoDB, and clear cached config:

```bash
php artisan optimize:clear
```

### TOTP setup routes are missing

Confirm `Features::twoFactorAuthentication()` is enabled in `config/fortify.php` and that the user model uses `TwoFactorAuthenticatable`.

## Release notes for maintainers

This package is intended for Packagist distribution. Before tagging a release:

```bash
composer validate --strict
php -l src/OxalisServiceProvider.php
php -l src/Http/Middleware/SecurityHeaders.php
```

In a host app, also run:

```bash
php artisan test
```

Packagist updates from Git tags. Use semantic versioning; Oxalis v2 is a breaking security-hardening release compared with the older custom route/runtime architecture.
