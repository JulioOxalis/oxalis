<?php

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

    'views' => [
        'login' => 'auth.login',
        'register' => 'auth.register',
        'forgot_password' => 'auth.forgot-password',
        'reset_password' => 'auth.reset-password',
        'verify_email' => 'auth.verify-email',
        'two_factor_challenge' => 'auth.two-factor-challenge',
        'confirm_password' => 'auth.confirm-password',
    ],

    'database' => [
        /*
         * Passkeys, sessions, database cache, and database queue records must
         * be stored on a SQL connection. If the app's user database is MongoDB,
         * set OXALIS_SECURITY_CONNECTION to a dedicated SQL connection such as
         * "security".
         */
        'security_connection' => env('OXALIS_SECURITY_CONNECTION', env('SESSION_CONNECTION', env('DB_CONNECTION', 'mysql'))),
    ],

    'security' => [
        'password_confirmation_timeout' => (int) env('OXALIS_CONFIRM_TIMEOUT', 10800),
        'login_attempts_per_minute' => (int) env('OXALIS_LOGIN_RATE_LIMIT', 5),
        'two_factor_attempts_per_minute' => (int) env('OXALIS_TOTP_RATE_LIMIT', 5),
        'passkey_attempts_per_minute' => (int) env('OXALIS_PASSKEY_RATE_LIMIT', 10),
    ],
];
