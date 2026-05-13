# Julio Oxalis

**Advanced, multi-method authentication for Laravel.**

Drop it in, run one Artisan command, and your app gets WebAuthn passkeys, magic links, OTP, TOTP, social login, step-up auth, and rate-limited security — with zero boilerplate.

---

## Features

- 🔑 **Passkeys (WebAuthn / FIDO2)** — fingerprint, Face ID, hardware keys
- 📧 **Magic Link** — one-click email sign-in
- 🔢 **Email OTP** — 6-digit code, bcrypt-hashed, 5-attempt lockout
- 📱 **TOTP (2FA)** — Google Authenticator, Authy, enforced on every login method
- 🔒 **Password login** — bcrypt, DB lockout, forgot-password flow
- 🌐 **Social login** — Google & GitHub OAuth
- ⚡ **Smart Dispatch** — one field, Oxalis picks the best method automatically
- 🛡️ **Step-up auth** — protect sensitive routes with fresh TOTP/passkey verification
- 📊 **Auth event log** — every attempt recorded, viewable at `/oxalis/stats`
- 🚫 **IP rate limiting** — DB-backed, works with any SQL connection
- 🖥️ **Admin panel** — user management, lockouts, invite codes, webhooks
- 📨 **Webhooks** — signed HTTP payloads on every auth event
- 🎟️ **Invite-only mode** — restrict registration to holders of a valid invite code
- 🌍 **Domain allowlist** — limit registration to specific email domains

---

## Requirements

- PHP 8.2+
- Laravel 11+
- MySQL / PostgreSQL / SQLite (for Oxalis tables)

> If your app's default connection is MongoDB, set `OXALIS_DB_CONNECTION` to a separate SQL connection — Oxalis stores auth data in SQL while your app data stays on MongoDB.

---

## Installation

```bash
composer require julio/oxalis
php artisan oxalis:install
```

The interactive wizard configures everything — auth methods, social credentials, redirects, and runs migrations automatically.

---

## Reconfiguring after install

Forgot to enable an auth method? Just edit `.env` directly — no need to reinstall.

**Toggle any method on/off:**
```env
OXALIS_ENABLE_PASSKEY=true
OXALIS_ENABLE_MAGIC_LINK=true
OXALIS_ENABLE_EMAIL_OTP=true
OXALIS_ENABLE_TOTP=true
OXALIS_ENABLE_PASSWORD=true
OXALIS_SMART_DISPATCH=false
```

**Enable social login later:**
```env
OXALIS_ENABLE_SOCIAL=true

OXALIS_GOOGLE_ENABLED=true
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-secret
GOOGLE_REDIRECT_URI=https://myapp.com/oxalis/social/google/callback

OXALIS_GITHUB_ENABLED=true
GITHUB_CLIENT_ID=your-client-id
GITHUB_CLIENT_SECRET=your-secret
GITHUB_REDIRECT_URI=https://myapp.com/oxalis/social/github/callback
```

Or re-run the wizard at any time — it updates existing values in-place:
```bash
php artisan oxalis:install
```

---

## Dev mode

When `APP_ENV=local`, Oxalis shows OTP codes and magic link URLs directly on-screen so you can test without email. **These never appear in production** (`APP_ENV=production`).

---

## Updating

```bash
composer update julio/oxalis
php artisan migrate
```

Run `migrate` after updating in case new migrations were added in the release.

---

## Uninstalling

```bash
php artisan oxalis:remove
composer remove julio/oxalis
```

`oxalis:remove` cleans up `.env` variables, config, published views, route redirects, and optionally drops the database tables.

---

## Quick start

```
/oxalis/login      → main login page
/oxalis/register   → 3-step registration (details → verify email → set password)
/oxalis/start      → Smart Dispatch (one-field sign-in)
/oxalis/account    → user account settings (passkeys, TOTP, sessions)
/oxalis/admin      → admin panel (requires OXALIS_ADMIN=true)
/oxalis/stats      → auth analytics dashboard
/oxalis/docs       → full documentation
```

---

## Artisan commands

```bash
# Installation & removal
php artisan oxalis:install          # interactive setup wizard
php artisan oxalis:remove           # removal wizard

# User management
php artisan oxalis:user             # interactive menu
php artisan oxalis:user create      # create a user
php artisan oxalis:user list        # list all users
php artisan oxalis:user delete      # delete a user and all their auth data

# Admin panel
php artisan oxalis:admin                    # interactive menu
php artisan oxalis:admin reset-password     # reset the admin password
php artisan oxalis:admin reset              # clear all admin credentials
php artisan oxalis:admin status             # show admin setup status

# Auth event log
php artisan oxalis:log                      # last 20 auth events
php artisan oxalis:log --user=email@x.com  # filter by user
php artisan oxalis:log --method=passkey    # filter by method
php artisan oxalis:log --limit=50          # change result count
```

---

## Admin panel

Enable with:
```env
OXALIS_ADMIN=true
OXALIS_ADMIN_GATE=admin   # optional — ties to a Laravel Gate
```

First visit at `/oxalis/admin` runs a one-time setup wizard to create admin credentials and optionally enable TOTP for the admin login. The panel provides:

- User table with search and filters
- Auth event log
- Lockout management (view, unlock, bulk clear)
- Invite code management
- Webhook configuration
- KPI cards (total users, active lockouts, login counts)

If you forget your admin password: `php artisan oxalis:admin reset-password`

---

## Registration controls

**Domain allowlist** — only allow registration from specific email domains:
```env
OXALIS_ALLOWED_DOMAINS=mycompany.com,partner.org
```

**Invite-only** — require a valid invite code to register:
```env
OXALIS_INVITE_ONLY=true
```
Generate codes in the admin panel at `/oxalis/admin/invites`. Each code has a configurable max-use count and optional expiry.

---

## Webhooks

```env
OXALIS_WEBHOOKS=true
```

Configure endpoints in the admin panel. Every auth event POSTs a signed JSON payload:

```json
{
  "event": "login",
  "fired_at": "2026-05-13T14:32:00+00:00",
  "payload": { "user_id": "sha256-hash", "method": "passkey", "ip": "1.2.3.4" }
}
```

Verify the `X-Oxalis-Signature: sha256=...` header with your webhook secret. After 10 consecutive failures the webhook auto-disables.

---

## Blade component

```blade
@auth
    <x-oxalis-user-menu />
@endauth
```

Renders a styled user avatar dropdown with account settings link and sign-out.

---

## Step-up auth middleware

```php
Route::middleware(['auth', 'oxalis.step-up'])->group(function () {
    Route::get('/billing', BillingController::class);
});
```

Requires fresh TOTP or passkey verification before accessing protected routes. Grants a 15-minute grace window after verification.

---

## Configuration

```bash
php artisan vendor:publish --tag=oxalis-config
php artisan vendor:publish --tag=oxalis-views
```

Key `.env` variables:

| Variable | Default | Description |
|---|---|---|
| `OXALIS_PREFIX` | `oxalis` | URL prefix — change to `auth` for `/auth/login` |
| `OXALIS_HOME` | `/dashboard` | Redirect after successful login |
| `OXALIS_RP_ID` | APP_URL host | WebAuthn Relying Party ID |
| `OXALIS_ORIGINS` | APP_URL | Allowed WebAuthn origins (comma-separated) |
| `OXALIS_DB_CONNECTION` | app default | SQL connection for Oxalis tables |
| `OXALIS_ENABLE_PASSKEY` | `true` | Enable passkey login |
| `OXALIS_ENABLE_MAGIC_LINK` | `true` | Enable magic link login |
| `OXALIS_ENABLE_EMAIL_OTP` | `true` | Enable email OTP login |
| `OXALIS_ENABLE_TOTP` | `true` | Enable TOTP 2FA |
| `OXALIS_ENABLE_PASSWORD` | `true` | Enable password login |
| `OXALIS_ENABLE_SOCIAL` | `false` | Enable social OAuth |
| `OXALIS_SMART_DISPATCH` | `false` | Enable one-field Smart Dispatch |
| `OXALIS_LOGIN_NOTIFICATION` | `false` | Email user on every new sign-in |
| `OXALIS_LOCKOUT_ATTEMPTS` | `5` | Failed attempts before lockout |
| `OXALIS_LOCKOUT_MINUTES` | `15` | Lockout duration in minutes |
| `OXALIS_TOTP_TRUST` | `true` | Allow "remember this device" for TOTP |
| `OXALIS_TOTP_TRUST_DAYS` | `30` | Days a trusted device skips TOTP |
| `OXALIS_ADMIN` | `false` | Enable the admin panel |
| `OXALIS_ADMIN_GATE` | `null` | Laravel Gate to additionally protect admin |
| `OXALIS_ALLOWED_DOMAINS` | _(any)_ | Comma-separated allowed registration domains |
| `OXALIS_INVITE_ONLY` | `false` | Require invite code to register |
| `OXALIS_WEBHOOKS` | `false` | Enable webhook delivery |
| `OXALIS_ACCOUNT_DELETION` | `true` | Show account deletion option to users |
| `OXALIS_DELETE_USER_MODEL` | `true` | Also delete the user row on account deletion |
| `OXALIS_REQUIRE_ATTESTATION` | `false` | Require attestation for passkey registration |

---

## Events

```php
use Oxalis\Events\UserLoggedIn;      // every successful login — $user, $method, $ip, $userAgent
use Oxalis\Events\LoginFailed;       // every failed attempt — $method, $ip
use Oxalis\Events\TotpEnabled;       // user confirms TOTP setup — $user
use Oxalis\Events\PasskeyRegistered; // user enrolls a passkey — $user, $passkey
use Oxalis\Events\StepUpVerified;    // step-up auth passed — $user, $method
```

---

## License

MIT © [JulioOxalis](https://github.com/JulioOxalis)
