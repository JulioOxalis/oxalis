# Julio Oxalis

**Advanced, multi-method authentication for Laravel.**

Drop it in, run one Artisan command, and your app gets WebAuthn passkeys, magic links, OTP, TOTP, social login, step-up auth, QR login, and rate-limited security — with zero boilerplate. Six built-in themes, five layout variants, and a deep customisation API.

---

## Features

- 🔑 **Passkeys (WebAuthn / FIDO2)** — fingerprint, Face ID, hardware keys
- 📧 **Magic Link** — one-click email sign-in
- 🔢 **Email OTP** — 6-digit code, bcrypt-hashed, 5-attempt lockout
- 📱 **TOTP (2FA)** — Google Authenticator, Authy, enforced on every login method
- 🔒 **Password login** — bcrypt, DB lockout, forgot-password flow
- 🌐 **Social login** — Google & GitHub OAuth
- ⚡ **Smart Dispatch** — one field, Oxalis picks the best method automatically
- 📷 **QR Login** — authenticated phone approves a desktop session by scanning a code
- 🛡️ **Step-up auth** — protect sensitive routes with fresh TOTP/passkey verification
- 🎨 **6 built-in themes + custom** — indigo, neon, aurora, obsidian, ember, frost
- 🖼️ **5 layout variants** — card, split, bare (holographic), glass, float
- 📊 **Auth event log** — every attempt recorded, viewable at `/oxalis/stats`
- 🚫 **IP rate limiting** — DB-backed, works with any SQL connection
- 🔍 **Risk engine** — geo/VPN scoring, impossible-travel detection
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

## Themes

Six built-in themes, each with a distinct design language:

| Value | Description |
|---|---|
| `indigo` | Default. Soft indigo, pill buttons, light body bg |
| `neon` | Cyberpunk. Cyan glow, monospace font, sharp corners |
| `aurora` | Glassmorphism. Purple, deep dark bg, blurred card |
| `obsidian` | Brutalist minimal. Black/white, monospace, no border-radius |
| `ember` | Warm dark. Amber accent, rich dark background |
| `frost` | Light glassmorphism. Sky-blue, white frosted card |
| `custom` | Your own stylesheet (see below) |

```env
OXALIS_THEME=neon
```

### Custom theme

Publish the CSS token stub and edit it to match your brand:

```bash
php artisan oxalis:theme:publish
# then set:
OXALIS_THEME=custom
```

This creates `public/vendor/oxalis/theme.css` with every CSS variable documented. Alternatively, override only the primary accent color and Oxalis auto-derives the hover shade, soft fill, and button text contrast:

```env
OXALIS_PRIMARY_COLOR=#e11d48
```

`OXALIS_PRIMARY_COLOR` works on top of any built-in theme — you get the theme's design language but with your brand color.

---

## Layouts

Five layout variants controlled by a single env variable:

```env
OXALIS_LAYOUT=split
```

| Value | Description |
|---|---|
| `card` | Centered card on the body background *(default)* |
| `split` | Brand panel on the left, login form on the right |
| `bare` | Holographic: deep-space animated background, spinning rainbow border, glassmorphism card with shimmer |
| `glass` | Frosted glassmorphism card floating over animated pastel bokeh blobs |
| `float` | Elevated card with multi-layer shadow and hover-lift; brand/logo shown above the card |

> `bare` and `glass` always force dark mode regardless of the chosen theme.

### Split layout customisation

The left brand panel accepts any valid CSS background value. **Values containing `#` or spaces must be quoted in `.env`** or the parser strips the rest of the line as a comment.

```env
# Solid color
OXALIS_SPLIT_BG="#e11d48"

# Gradient
OXALIS_SPLIT_BG="linear-gradient(135deg,#e11d48 0%,#7c3aed 60%,#3b82f6 100%)"

# Image (full cover)
OXALIS_SPLIT_BG="url(/img/auth-panel.jpg) center/cover no-repeat"

# Image with dark overlay
OXALIS_SPLIT_BG="linear-gradient(rgba(0,0,0,.45),rgba(0,0,0,.45)),url(/img/auth-panel.jpg) center/cover no-repeat"

# Text color inside the panel
OXALIS_SPLIT_TEXT="#ffffff"   # white — for dark images/colors
OXALIS_SPLIT_TEXT="#1a1f36"  # dark  — for light backgrounds
```

Defaults: background → `var(--ox)` (your primary brand color), text → white.

### Branding

Displayed in the card header (all layouts) and the split brand panel:

```env
OXALIS_LOGO_URL=https://myapp.com/img/logo.svg   # shown above app name
OXALIS_TAGLINE="Welcome back"                     # subtitle under app name
OXALIS_SHOW_APP_NAME=true                         # show APP_NAME in the header
```

On the **split layout**, `OXALIS_SHOW_APP_NAME` and `OXALIS_TAGLINE` appear **only in the brand panel** — they are intentionally suppressed inside the card to avoid duplication.

### Publishing partials

Override the brand panel or card header without touching package files:

```bash
php artisan vendor:publish --tag=oxalis-partials
# Edit: resources/views/vendor/oxalis/partials/split-brand.blade.php
#       resources/views/vendor/oxalis/partials/card-header.blade.php
```

---

## Blade injection points

Inject content into any auth page without overriding entire views:

```blade
{{-- Above the card --}}
@push('oxalis:before-card') ... @endpush

{{-- Inside the card, before content --}}
@push('oxalis:card-top') ... @endpush

{{-- Inside the card, after content --}}
@push('oxalis:card-bottom') ... @endpush

{{-- Below the card --}}
@push('oxalis:after-card') ... @endpush

{{-- In <head> (custom CSS) --}}
@push('styles') <link rel="stylesheet" href="..."> @endpush

{{-- Before </body> (custom JS) --}}
@push('scripts') <script src="..."></script> @endpush
```

---

## QR Login

Let an authenticated phone approve a desktop login session by scanning a QR code.

```env
OXALIS_QR_LOGIN=true
OXALIS_QR_TTL=90    # seconds the code stays valid
```

The QR code links to the phone's approval URL. The desktop polls every 2 seconds; on approval it exchanges the token for a full session and redirects to the home route.

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

# Theme
php artisan oxalis:theme:publish           # publish CSS token stub → public/vendor/oxalis/theme.css
php artisan oxalis:theme:publish --force   # overwrite existing

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

# Maintenance
php artisan oxalis:prune-logs              # delete auth events older than OXALIS_LOG_RETENTION_DAYS
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

## Security & privacy

### Security headers
```env
OXALIS_SECURITY_HEADERS=true   # X-Frame-Options, CSP, Referrer-Policy, etc.
```

### IP privacy
```env
OXALIS_IP_ANONYMIZE=true   # zero last IPv4 octet / truncate IPv6 to /64 before storage
OXALIS_STORE_IP=false      # never write IP to auth_events at all
```

### Log retention
```env
OXALIS_LOG_RETENTION_DAYS=365   # 0 = keep forever
```
Schedule `oxalis:prune-logs` in your app's scheduler to enforce this.

### Credential breach check
```env
OXALIS_BREACH_CHECK=true
```
After a successful password login, Oxalis checks the credential against the HaveIBeenPwned k-anonymity API. A warning is shown — the user is not blocked unless you customise the behavior.

### Risk engine
```env
OXALIS_RISK_ENGINE=true
OXALIS_RISK_THRESHOLD=40    # score 0–100; above this triggers login-context email
OXALIS_RISK_GEO=true        # impossible-travel detection via ip-api.com (free)
OXALIS_RISK_VPN=false       # VPN/proxy detection via ip-api.com
OXALIS_LOGIN_CONTEXT_EMAIL=true   # send "new device / unusual location" email
```

### Concurrent session limit
```env
OXALIS_MAX_SESSIONS=3   # 0 = unlimited; oldest sessions revoked when exceeded
```

---

## Passkey options

```env
OXALIS_PASSKEY_ONLY=true          # disable all other auth methods
OXALIS_PASSKEY_NUDGE_MAX=3        # max dismissals of "upgrade to passkey" prompt (0 = never show)
OXALIS_REQUIRE_ATTESTATION=true   # enterprise: reject 'none' attestation
```

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

## Configuration reference

```bash
php artisan vendor:publish --tag=oxalis-config
```

### Auth methods

| Variable | Default | Description |
|---|---|---|
| `OXALIS_ENABLE_PASSKEY` | `true` | Enable passkey login |
| `OXALIS_ENABLE_MAGIC_LINK` | `true` | Enable magic link login |
| `OXALIS_ENABLE_EMAIL_OTP` | `true` | Enable email OTP login |
| `OXALIS_ENABLE_TOTP` | `true` | Enable TOTP 2FA |
| `OXALIS_ENABLE_PASSWORD` | `true` | Enable password login |
| `OXALIS_ENABLE_SOCIAL` | `false` | Enable social OAuth |
| `OXALIS_SMART_DISPATCH` | `false` | One-field Smart Dispatch |
| `OXALIS_PASSKEY_ONLY` | `false` | Disable all methods except passkeys |
| `OXALIS_PASSKEY_NUDGE_MAX` | `3` | Max nudge dismissals (0 = never show) |

### Appearance

| Variable | Default | Description |
|---|---|---|
| `OXALIS_THEME` | `indigo` | Theme: `indigo` `neon` `aurora` `obsidian` `ember` `frost` `custom` |
| `OXALIS_PRIMARY_COLOR` | _(theme default)_ | Hex override e.g. `#e11d48`; auto-derives hover/fill/contrast |
| `OXALIS_LAYOUT` | `card` | Layout: `card` `split` `bare` `glass` `float` |
| `OXALIS_LOGO_URL` | _(none)_ | Public URL to PNG/SVG logo |
| `OXALIS_TAGLINE` | _(none)_ | Subtitle shown under app name (quote in `.env`) |
| `OXALIS_SHOW_APP_NAME` | `false` | Show APP_NAME in card header / split panel |
| `OXALIS_SPLIT_BG` | `var(--ox)` | Split panel background — any CSS value; **must quote in `.env`** |
| `OXALIS_SPLIT_TEXT` | `#fff` | Split panel text color; **must quote in `.env`** |

### Routing

| Variable | Default | Description |
|---|---|---|
| `OXALIS_PREFIX` | `oxalis` | URL prefix — change to `auth` for `/auth/login` |
| `OXALIS_HOME` | `/dashboard` | Redirect after successful login |
| `OXALIS_RP_ID` | APP_URL host | WebAuthn Relying Party ID |
| `OXALIS_ORIGINS` | APP_URL | Allowed WebAuthn origins (comma-separated) |
| `OXALIS_DB_CONNECTION` | app default | SQL connection for Oxalis tables |

### Security & sessions

| Variable | Default | Description |
|---|---|---|
| `OXALIS_LOCKOUT_ATTEMPTS` | `5` | Failed attempts before lockout |
| `OXALIS_LOCKOUT_MINUTES` | `15` | Lockout duration in minutes |
| `OXALIS_TOTP_TRUST` | `true` | Allow "remember this device" for TOTP |
| `OXALIS_TOTP_TRUST_DAYS` | `30` | Days a trusted device skips TOTP |
| `OXALIS_MAX_SESSIONS` | `0` | Max concurrent sessions (0 = unlimited) |
| `OXALIS_SECURITY_HEADERS` | `true` | Add security headers to all responses |
| `OXALIS_REQUIRE_ATTESTATION` | `false` | Require attestation for passkey registration |
| `OXALIS_BREACH_CHECK` | `false` | Check passwords against HaveIBeenPwned |

### Risk & privacy

| Variable | Default | Description |
|---|---|---|
| `OXALIS_RISK_ENGINE` | `false` | Enable login risk scoring |
| `OXALIS_RISK_THRESHOLD` | `40` | Score threshold to trigger context email |
| `OXALIS_RISK_GEO` | `true` | Impossible-travel detection |
| `OXALIS_RISK_VPN` | `false` | VPN/proxy detection |
| `OXALIS_LOGIN_CONTEXT_EMAIL` | `false` | Email on high-risk / new-device login |
| `OXALIS_LOGIN_NOTIFICATION` | `false` | Email on every successful sign-in |
| `OXALIS_IP_ANONYMIZE` | `false` | Truncate stored IPs before saving |
| `OXALIS_STORE_IP` | `true` | Write IP to auth_events |
| `OXALIS_LOG_RETENTION_DAYS` | `365` | Days to keep auth events (0 = forever) |

### Admin & registration

| Variable | Default | Description |
|---|---|---|
| `OXALIS_ADMIN` | `false` | Enable the admin panel |
| `OXALIS_ADMIN_GATE` | _(none)_ | Laravel Gate to additionally protect admin |
| `OXALIS_ALLOWED_DOMAINS` | _(any)_ | Comma-separated allowed registration domains |
| `OXALIS_INVITE_ONLY` | `false` | Require invite code to register |
| `OXALIS_WEBHOOKS` | `false` | Enable webhook delivery |
| `OXALIS_ACCOUNT_DELETION` | `true` | Show account deletion option to users |
| `OXALIS_DELETE_USER_MODEL` | `true` | Also delete the user row on account deletion |

### QR Login

| Variable | Default | Description |
|---|---|---|
| `OXALIS_QR_LOGIN` | `false` | Enable QR login |
| `OXALIS_QR_TTL` | `90` | Seconds before QR code expires |

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

## Changelog

### v1.8.0
- `bare` layout redesigned as holographic: animated deep-space orbs + grid overlay, spinning conic-gradient rainbow border, glassmorphism inner card with shimmer sweep
- New `glass` layout: frosted card over animated pastel bokeh blobs (always dark)
- New `float` layout: deep shadow card with hover-lift, brand shown above card
- `split` layout: app name/tagline no longer duplicated in the card — they show only in the brand panel
- `bare` and `glass` now force `data-bs-theme=dark` regardless of chosen theme

### v1.7.1
- Fixed QR code rendering — switched to `qrcodejs@1.0.0` (correct browser library)
- Fixed PHP variable conflict (`$m` → `$oxM`) between layout and login view
- Split panel background: supports solid color, gradient, image, or image+overlay
- `OXALIS_SPLIT_BG` / `OXALIS_SPLIT_TEXT` — must be quoted in `.env` for CSS values containing `#`

### v1.7.0
- Six built-in themes: indigo, neon, aurora, obsidian, ember, frost
- Custom theme via `php artisan oxalis:theme:publish`
- `OXALIS_PRIMARY_COLOR` — single hex auto-derives hover, fill, and button contrast
- Five layout variants: card, split, bare, glass, float
- Branding: `OXALIS_LOGO_URL`, `OXALIS_TAGLINE`, `OXALIS_SHOW_APP_NAME`
- Blade injection stacks: `oxalis:before-card`, `oxalis:card-top`, `oxalis:card-bottom`, `oxalis:after-card`, `styles`, `scripts`
- Publishable partials: `php artisan vendor:publish --tag=oxalis-partials`
- QR Login feature
- Security headers, IP anonymization, log retention, breach check, risk engine, session limits

---

## License

MIT © [JulioOxalis](https://github.com/JulioOxalis)
