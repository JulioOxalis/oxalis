<?php
return [

    // ── Database connection ───────────────────────────────────────────────────
    // Set OXALIS_DB_CONNECTION if your app uses a non-relational default DB
    // (e.g. MongoDB). Oxalis requires SQLite, MySQL, or PostgreSQL.
    'connection' => env('OXALIS_DB_CONNECTION', null),

    // ── Relying Party (WebAuthn / Passkeys) ───────────────────────────────────
    'rp_id'   => env('OXALIS_RP_ID', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost'),
    'rp_name' => env('OXALIS_RP_NAME', env('APP_NAME', 'App')),
    'origins' => array_filter(explode(',', env('OXALIS_ORIGINS', env('APP_URL', 'http://localhost')))),

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

];
