<?php

use Oxalis\Support\WebAuthnConfig;

return [

    // ── Database connection ───────────────────────────────────────────────────
    // Set OXALIS_DB_CONNECTION if your app uses a non-relational default DB
    // (e.g. MongoDB). Oxalis requires SQLite, MySQL, or PostgreSQL.
    'connection' => env('OXALIS_DB_CONNECTION', null),

    // ── Relying Party (WebAuthn / Passkeys) ───────────────────────────────────
    'rp_id'   => env('OXALIS_RP_ID', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost'),
    'rp_name' => env('OXALIS_RP_NAME', env('APP_NAME', 'App')),
    // When OXALIS_ORIGINS is unset, include common localhost variants (not just APP_URL).
    'origins' => env('OXALIS_ORIGINS')
        ? array_filter(explode(',', env('OXALIS_ORIGINS')))
        : WebAuthnConfig::suggestedOrigins(env('APP_URL', 'http://localhost')),

    // ── Theme ─────────────────────────────────────────────────────────────────
    // Built-in presets: indigo | neon | aurora | obsidian | ember | frost
    // Each has a distinct design language (not just a color swap).
    //
    // Use "custom" to load your own stylesheet:
    //   php artisan vendor:publish --tag=oxalis-theme
    //   Edit: public/vendor/oxalis/theme.css
    //
    // To override individual Blade templates:
    //   php artisan vendor:publish --tag=oxalis-views
    //   Edit files in: resources/views/vendor/oxalis/
    //   Oxalis auto-detects vendor overrides — no config change needed.
    //
    // To override just the card-header / logo partial:
    //   php artisan vendor:publish --tag=oxalis-partials
    //   Edit files in: resources/views/vendor/oxalis/partials/
    'theme' => env('OXALIS_THEME', 'indigo'),

    // Override the primary brand color for any theme. Hex value.
    // e.g. OXALIS_PRIMARY_COLOR=#e11d48
    // Oxalis auto-derives hover (--ox-dk), soft-fill (--ox-sf), and
    // button text contrast (--ox-btn-fg) from this single value.
    'theme_color' => env('OXALIS_PRIMARY_COLOR', null),

    // ── Layout variant ────────────────────────────────────────────────────────
    // card  — centered card on body bg (default)
    // split — brand panel on the left, form on the right
    // bare  — holographic: dark space bg, animated orbs, spinning rainbow border
    // glass — frosted glassmorphism card over animated pastel bokeh blobs
    // float — elevated card with brand/logo displayed above it; hover-lift effect
    'layout' => env('OXALIS_LAYOUT', 'card'),

    // ── Branding ─────────────────────────────────────────────────────────────
    // Displayed in the card header above the form.
    // set OXALIS_LOGO_URL to a publicly accessible image (PNG / SVG).
    // set OXALIS_TAGLINE to a short subtitle shown under the app name.
    // OXALIS_SHOW_APP_NAME=false hides the app name (useful when logo says it all).
    'brand' => [
        'logo_url'      => env('OXALIS_LOGO_URL'),
        'tagline'       => env('OXALIS_TAGLINE'),
        'show_app_name' => env('OXALIS_SHOW_APP_NAME', false),

        // ── Split-layout brand panel customisation ────────────────────────────
        // split_bg accepts any valid CSS background value. IMPORTANT: values
        // containing # or spaces MUST be quoted in .env or the parser will
        // strip everything from the first unquoted # onward.
        //
        //   Solid color  → OXALIS_SPLIT_BG="#e11d48"
        //   Gradient     → OXALIS_SPLIT_BG="linear-gradient(135deg,#e11d48 0%,#7c3aed 60%,#3b82f6 100%)"
        //   Image        → OXALIS_SPLIT_BG="url(/img/auth-bg.jpg) center/cover no-repeat"
        //   Image+overlay→ OXALIS_SPLIT_BG="linear-gradient(rgba(0,0,0,.45),rgba(0,0,0,.45)),url(/img/auth-bg.jpg) center/cover no-repeat"
        //
        // Defaults to var(--ox) (your primary brand color).
        'split_bg'   => env('OXALIS_SPLIT_BG'),

        // Text color inside the brand panel. Quote the value in .env:
        //   OXALIS_SPLIT_TEXT="#ffffff"   (light text for dark backgrounds)
        //   OXALIS_SPLIT_TEXT="#1a1f36"   (dark text for light backgrounds)
        // Defaults to var(--ox-btn-fg,#fff).
        'split_text' => env('OXALIS_SPLIT_TEXT'),
    ],

    // ── Passkey-only mode ─────────────────────────────────────────────────────
    // true = disables ALL other auth methods (password, magic link, OTP, social).
    // Use for high-security apps where FIDO2 is the only acceptable factor.
    'passkey_only' => env('OXALIS_PASSKEY_ONLY', false),

    // Maximum times a user can dismiss the "upgrade to passkey" nudge.
    // 0 = never show the nudge. After this many dismissals it stops appearing.
    'passkey_nudge_max' => (int) env('OXALIS_PASSKEY_NUDGE_MAX', 3),

    'passkey_recovery' => [
        'enabled' => env('OXALIS_PASSKEY_RECOVERY', true),
        'codes'   => (int) env('OXALIS_PASSKEY_RECOVERY_CODES', 8),
    ],

    // ── Enabled auth methods ──────────────────────────────────────────────────
    'methods' => [
        'passkey'    => env('OXALIS_ENABLE_PASSKEY', true),
        'magic_link' => env('OXALIS_ENABLE_MAGIC_LINK', true),
        'email_otp'  => env('OXALIS_ENABLE_EMAIL_OTP', true),
        'totp'       => env('OXALIS_ENABLE_TOTP', true),
        'password'   => env('OXALIS_ENABLE_PASSWORD', true),
        'social'     => env('OXALIS_ENABLE_SOCIAL', false),
    ],

    // ── Smart dispatch (One-Field Auth) ───────────────────────────────────────
    'smart_dispatch' => env('OXALIS_SMART_DISPATCH', false),

    // ── Email OTP ─────────────────────────────────────────────────────────────
    'otp' => [
        'length'       => 6,
        'expires_in'   => 5,
        'max_attempts' => 5,
    ],

    // ── Magic link ────────────────────────────────────────────────────────────
    'magic_link' => [
        'expires_in' => 15,
    ],

    // ── Trusted device ────────────────────────────────────────────────────────
    'trusted_device' => [
        'days' => 30,
    ],

    // ── Step-up authentication ────────────────────────────────────────────────
    'step_up' => [
        'ttl_minutes' => 15,
    ],

    // ── Rate limiting + lockout ───────────────────────────────────────────────
    'lockout' => [
        'max_attempts' => env('OXALIS_LOCKOUT_ATTEMPTS', 5),
        'minutes'      => env('OXALIS_LOCKOUT_MINUTES', 15),
    ],

    // Global auth rate limiter (all POST auth endpoints, per IP)
    'rate_limit' => [
        'per_minute'          => env('OXALIS_RATE_PER_MINUTE', 30),   // general auth actions
        'send_per_window'     => env('OXALIS_SEND_PER_WINDOW', 5),    // OTP/magic-link sends
        'send_window_minutes' => env('OXALIS_SEND_WINDOW_MIN', 5),    // window for send limit
    ],

    // ── Passkey attestation ───────────────────────────────────────────────────
    // false = accept any attestation (default, works on all devices)
    // true  = reject 'none' attestation (enterprise — requires verifiable device)
    'require_attestation' => env('OXALIS_REQUIRE_ATTESTATION', false),

    // ── Social login ─────────────────────────────────────────────────────────
    'social' => [
        'google' => [
            'enabled'       => env('OXALIS_GOOGLE_ENABLED', false),
            'client_id'     => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect'      => env('GOOGLE_REDIRECT_URI'),
        ],
        'github' => [
            'enabled'       => env('OXALIS_GITHUB_ENABLED', false),
            'client_id'     => env('GITHUB_CLIENT_ID'),
            'client_secret' => env('GITHUB_CLIENT_SECRET'),
            'redirect'      => env('GITHUB_REDIRECT_URI'),
        ],
    ],

    // ── Routing ───────────────────────────────────────────────────────────────
    // Change OXALIS_PREFIX to use a custom URL prefix, e.g. "auth" → /auth/login
    'routes' => [
        'prefix'     => env('OXALIS_PREFIX', 'oxalis'),
        'middleware' => ['web'],
        'home'       => env('OXALIS_HOME', '/dashboard'),
        'login'      => '/'.env('OXALIS_PREFIX', 'oxalis').'/login',
    ],

    // ── User model ────────────────────────────────────────────────────────────
    'user_model' => \App\Models\User::class,

    'table_prefix' => 'oxalis_',

    // ── Login notification email ──────────────────────────────────────────────
    'login_notification' => env('OXALIS_LOGIN_NOTIFICATION', false),

    // ── TOTP trusted device ("remember this device") ─────────────────────────
    'totp_trust' => [
        'enabled' => env('OXALIS_TOTP_TRUST', true),
        'days'    => env('OXALIS_TOTP_TRUST_DAYS', 30),
    ],

    // ── Admin panel (/oxalis/admin) ───────────────────────────────────────────
    // Disabled by default — set OXALIS_ADMIN=true to enable.
    // Optionally gate with a Laravel Gate: OXALIS_ADMIN_GATE=admin
    'admin' => [
        'enabled' => env('OXALIS_ADMIN', false),
        'gate'    => env('OXALIS_ADMIN_GATE', null),
    ],

    // ── Account deletion ──────────────────────────────────────────────────────
    'account_deletion' => [
        'enabled'           => env('OXALIS_ACCOUNT_DELETION', true),
        'delete_user_model' => env('OXALIS_DELETE_USER_MODEL', true),
    ],

    // ── Email domain allowlist ────────────────────────────────────────────────
    // Comma-separated. Empty = allow all. e.g. "mycompany.com,partner.org"
    'allowed_domains' => env('OXALIS_ALLOWED_DOMAINS', ''),

    // ── Invite-only registration ──────────────────────────────────────────────
    'invites' => [
        'required' => env('OXALIS_INVITE_ONLY', false),
    ],

    // ── Webhooks ──────────────────────────────────────────────────────────────
    'webhooks' => [
        'enabled' => env('OXALIS_WEBHOOKS', false),
    ],

    // ── Anonymous telemetry (opt-in, default OFF) ─────────────────────────────
    // Sends: package version, PHP/Laravel version, enabled methods (booleans),
    // and a one-way SHA-256 hash of APP_URL. Never any user PII.
    'telemetry'          => env('OXALIS_TELEMETRY', false),
    'telemetry_endpoint' => env('OXALIS_TELEMETRY_ENDPOINT', null),

    // ── Security headers ──────────────────────────────────────────────────────
    // Adds X-Frame-Options, X-Content-Type-Options, Referrer-Policy,
    // X-XSS-Protection, and Permissions-Policy to all Oxalis responses.
    'security_headers' => env('OXALIS_SECURITY_HEADERS', true),

    // ── IP privacy ────────────────────────────────────────────────────────────
    // ip_anonymize: zero last IPv4 octet / truncate IPv6 to /64 before storage
    // store_ip:     false = never write IP to auth_events at all
    'ip_anonymize' => env('OXALIS_IP_ANONYMIZE', false),
    'store_ip'     => env('OXALIS_STORE_IP', true),

    // Days to keep auth_event records (0 = keep forever).
    // Run `php artisan oxalis:prune-logs` on a schedule to enforce this.
    'log_retention_days' => (int) env('OXALIS_LOG_RETENTION_DAYS', 365),

    // ── Credential breach check (HaveIBeenPwned k-anonymity API) ─────────────
    // When enabled, password logins are checked against HIBP after success.
    // A warning is shown — the user is NOT blocked unless you customise this.
    'breach_check' => env('OXALIS_BREACH_CHECK', false),

    // ── Risk engine ───────────────────────────────────────────────────────────
    // Scores each login 0–100.  At or above threshold, login context email fires.
    // geo:  use ip-api.com (free) to detect impossible travel
    // vpn:  use ip-api.com proxy/hosting fields (free tier, 45 req/min)
    'risk' => [
        'enabled'   => env('OXALIS_RISK_ENGINE', false),
        'threshold' => (int) env('OXALIS_RISK_THRESHOLD', 40),
        'geo'       => env('OXALIS_RISK_GEO', true),
        'vpn'       => env('OXALIS_RISK_VPN', false),
    ],

    // ── Concurrent session limit ──────────────────────────────────────────────
    // 0 = unlimited. When exceeded, oldest sessions are revoked automatically.
    'max_sessions' => (int) env('OXALIS_MAX_SESSIONS', 0),

    // ── Login context email ───────────────────────────────────────────────────
    // Sends a "new device / unusual location" email when risk score > threshold.
    // Requires OXALIS_RISK_ENGINE=true.
    'login_context_email' => env('OXALIS_LOGIN_CONTEXT_EMAIL', false),

    // ── QR Login ─────────────────────────────────────────────────────────────
    // Scan a QR code with an authenticated phone to approve a desktop login.
    // ttl: seconds the QR code stays valid (default 90)
    'qr_login' => [
        'enabled' => env('OXALIS_QR_LOGIN', false),
        'ttl'     => (int) env('OXALIS_QR_TTL', 90),
    ],

    // ── Ultrasonic proximity authentication ───────────────────────────────────
    // Desktop plays an inaudible (17–20 kHz) token; an authenticated phone
    // running in the browser hears it, decodes it, and approves the login.
    // Requires: Chrome/Edge on both devices (Web Audio API + getUserMedia).
    // ttl: seconds the ultrasonic token stays valid (default 30)
    'ultrasonic' => [
        'enabled' => env('OXALIS_ULTRASONIC', false),
        'ttl'     => (int) env('OXALIS_ULTRASONIC_TTL', 30),
    ],

];
