# Oxalis

Oxalis is the configuration boundary for this application's authentication stack. It delegates protocol and security-sensitive work to Laravel Fortify and Laravel Passkeys while keeping the interface in app-owned Blade components.

## Compatibility

- PHP 8.2+
- Laravel 12 or 13
- Laravel Fortify 1.40+
- Laravel Passkeys 0.2+

## Enabled authentication

- password login and registration
- password reset
- email verification
- TOTP with one-time recovery codes
- WebAuthn passkeys

Magic links, social login, QR approval, Bluetooth, ultrasonic pairing, risk scoring, panic flows, and webhooks are intentionally outside the runtime.

## Security boundary

Oxalis v2 does not register its own authentication routes or controllers. The host Laravel app owns the canonical `/login` and `/register` routes through Fortify, and Laravel Passkeys owns the WebAuthn ceremonies. Disabled features must be disabled in Fortify/passkey configuration, not hidden only in the UI.

The legacy AuthX/Oxalis tables and experimental flows from the older branch are not shipped:

- no magic-link bearer-token table
- no social-login, Smart Dispatch, invite, admin, webhook, QR, ultrasonic, risk, or step-up runtime
- no package route file that can bypass Fortify throttling, CSRF, session regeneration, email verification, or TOTP

This keeps optional ideas such as magic links, social login, QR approval, risk scoring, and webhooks as future modules instead of default authentication paths.

## Customization

Publish `oxalis-config` or override `config/oxalis.php`. Every authentication screen is selected through the `views` map and branded through the `brand` map. The application may replace any view without changing Fortify or passkey protocol code.

Publish the SQL security migrations when installing from Packagist:

```bash
php artisan vendor:publish --tag=oxalis-migrations
```

Publish the default auth stylesheet when installing from Packagist:

```bash
php artisan vendor:publish --tag=oxalis-assets
```

Publish the default Blade views when the host app wants Oxalis pages:

```bash
php artisan vendor:publish --tag=oxalis-views
```

The app-owned auth layout should include `public/vendor/oxalis/auth.css` as a fallback and may also load the host application's Vite bundle. If pages appear unstyled during local development, delete a stale `public/hot` file or run the Vite dev server it points to.

Canonical view keys:

- `login`: `resources/views/auth/login.blade.php`
- `register`: `resources/views/auth/register.blade.php`
- `forgot_password`: `resources/views/auth/forgot-password.blade.php`
- `reset_password`: `resources/views/auth/reset-password.blade.php`
- `verify_email`: `resources/views/auth/verify-email.blade.php`
- `two_factor_challenge`: `resources/views/auth/two-factor-challenge.blade.php`
- `confirm_password`: `resources/views/auth/confirm-password.blade.php`

Shared auth UI components live in `resources/views/components/auth/layout.blade.php` and `resources/views/components/auth/field.blade.php`.

## Data layout

The `User` model may use MongoDB. Passkeys, database sessions, database cache, and database queues must use a SQL connection because Fortify, throttling, Laravel Passkeys, and WebAuthn ceremony state depend on SQL-style locking and insert behavior.

For Mongo-backed apps, create a dedicated SQL connection and point Oxalis at it:

```env
OXALIS_SECURITY_CONNECTION=security
SESSION_CONNECTION=security
DB_CACHE_CONNECTION=security
DB_CACHE_LOCK_CONNECTION=security
DB_QUEUE_CONNECTION=security
```

Mongo ObjectIds must cross this boundary as strings. The host app may bind a custom passkey verifier that performs credential locking and signature-counter updates on the SQL connection.

## Browser security headers

The package exposes `oxalis.security.headers`, which emits:

- `Content-Security-Policy`
- `X-Content-Type-Options`
- `Referrer-Policy`
- `Permissions-Policy`
- production HTTPS `Strict-Transport-Security`

Local development CSP allows Vite on `localhost`, `127.0.0.1`, and `[::1]`. Production passkeys should use exact HTTPS origins and a stable relying-party ID.

## Production requirements

- set `APP_ENV=production`, `APP_DEBUG=false`, and an HTTPS `APP_URL`
- set `FORCE_HTTPS=true`, `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, and `SESSION_SAME_SITE=lax`
- configure `TRUSTED_PROXIES` only with proxy addresses or CIDRs controlled by the deployment
- set `PASSKEYS_RELYING_PARTY_ID` to the exact registrable host and `PASSKEYS_ALLOWED_ORIGINS` to exact HTTPS origins
- provide a persistent SQL database for `OXALIS_SECURITY_CONNECTION`; do not use an ephemeral filesystem in production
- set `SESSION_CONNECTION=security`, `DB_CACHE_CONNECTION=security`, `DB_CACHE_LOCK_CONNECTION=security`, and `DB_QUEUE_CONNECTION=security` when the default user database is MongoDB
- use MongoDB PHP extension 2.4 or newer before deployment

Run `php artisan migrate`, `npm ci`, `npm run build`, and `php artisan test --compact` during deployment verification.
