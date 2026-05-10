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
- 🚫 **IP rate limiting** — DB-backed, MongoDB-safe

---

## Requirements

- PHP 8.2+
- Laravel 11 or 12
- MySQL / PostgreSQL / SQLite (for Oxalis tables)

---

## Installation

```bash
composer require julio/oxalis
php artisan oxalis:install
```

The interactive wizard configures everything — auth methods, social credentials, redirects, and runs migrations automatically.

---

## Quick start

```
/oxalis/login      → main login page
/oxalis/register   → 3-step registration (details → verify email → set password)
/oxalis/account    → user account settings (passkeys, TOTP, theme)
/oxalis/stats      → auth analytics dashboard
/oxalis/docs       → full documentation
```

---

## Blade component

```blade
@auth
    <x-oxalis-user-menu />
@endauth
```

Renders a styled user avatar dropdown with account settings link and sign-out.

---

## Configuration

```bash
php artisan vendor:publish --tag=oxalis-config
php artisan vendor:publish --tag=oxalis-views
```

Key `.env` variables:

```env
OXALIS_RP_ID=myapp.com
OXALIS_ORIGINS=https://myapp.com
OXALIS_ENABLE_PASSKEY=true
OXALIS_ENABLE_TOTP=true
OXALIS_ENABLE_MAGIC_LINK=true
OXALIS_ENABLE_EMAIL_OTP=true
OXALIS_ENABLE_PASSWORD=true
OXALIS_LOGIN_NOTIFICATION=false
```

---

## Events

```php
use Oxalis\Events\UserLoggedIn;  // fires on every successful login
use Oxalis\Events\TotpEnabled;   // fires when user confirms TOTP setup
```

---

## License

MIT © [JulioOxalis](https://github.com/JulioOxalis)
