<?php
namespace Oxalis\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Oxalis\Support\WebAuthnConfig;

class InstallCommand extends Command
{
    protected $signature   = 'oxalis:install {--force : Overwrite existing published files}';
    protected $description = 'Install oxalis — interactive setup wizard';

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <fg=blue;options=bold> ██████╗ ██╗  ██╗ █████╗ ██╗     ██╗███████╗</>');
        $this->line('  <fg=blue;options=bold>██╔═══██╗╚██╗██╔╝██╔══██╗██║     ██║██╔════╝</>');
        $this->line('  <fg=blue;options=bold>██║   ██║ ╚███╔╝ ███████║██║     ██║███████╗</>');
        $this->line('  <fg=blue;options=bold>██║   ██║ ██╔██╗ ██╔══██║██║     ██║╚════██║</>');
        $this->line('  <fg=blue;options=bold>╚██████╔╝██╔╝ ██╗██║  ██║███████╗██║███████║</>');
        $this->line('  <fg=blue;options=bold> ╚═════╝ ╚═╝  ╚═╝╚═╝  ╚═╝╚══════╝╚═╝╚══════╝</>');
        $this->line('  <fg=blue;options=bold>by</> <fg=white;options=bold,underscore>julio</>');
        $this->newLine();

        // ── 1. Publish config ─────────────────────────────────────────────────
        $this->callSilent('vendor:publish', ['--tag' => 'oxalis-config', '--force' => $this->option('force')]);
        $this->line('  <fg=green>✓</> config/oxalis.php published');

        // ── 2. Choose auth methods ────────────────────────────────────────────
        $this->newLine();
        $this->info('Which auth methods do you want to enable?');

        $allMethods = [
            'passkey'    => 'Passkey / WebAuthn  (fingerprint, Face ID, hardware key)',
            'magic_link' => 'Magic link          (click a link emailed to you)',
            'email_otp'  => 'Email OTP           (6-digit code emailed to you)',
            'totp'       => 'TOTP                (Google Authenticator, Authy)',
            'password'   => 'Password            (classic email + password)',
            'social'     => 'Social login        (Google, GitHub)',
        ];

        $chosen = $this->choice(
            'Select (comma-separated, default = all except social)',
            array_values($allMethods),
            '0,1,2,3,4',
            null,
            true,
        );

        // Map selected labels back to keys
        $labelToKey = array_flip($allMethods);
        $enabledKeys = array_filter(array_map(fn($label) => $labelToKey[$label] ?? null, $chosen));

        // ── 3. Smart dispatch ─────────────────────────────────────────────────
        $this->newLine();
        $smartDispatch = $this->confirm(
            '✦ Enable One-Field Auth (Smart Dispatch)? — single email field, auto-picks the best method per user',
            false,
        );

        // ── 4. Social credentials ─────────────────────────────────────────────
        $googleEnabled = false;
        $githubEnabled = false;
        $googleId = $googleSecret = $githubId = $githubSecret = null;

        if (in_array('social', $enabledKeys)) {
            $this->newLine();
            $googleEnabled = $this->confirm('Enable Google login?', false);
            if ($googleEnabled) {
                $googleId     = $this->ask('  Google Client ID');
                $googleSecret = $this->secret('  Google Client Secret');
            }
            $githubEnabled = $this->confirm('Enable GitHub login?', false);
            if ($githubEnabled) {
                $githubId     = $this->ask('  GitHub Client ID');
                $githubSecret = $this->secret('  GitHub Client Secret');
            }
        }

        // ── 5. Route prefix + home redirect ──────────────────────────────────
        $this->newLine();
        $prefix = $this->ask('Route prefix? (all Oxalis URLs will start with this)', 'oxalis');
        $prefix = preg_replace('/[^a-z0-9\-_]/i', '', trim($prefix, '/')) ?: 'oxalis';

        $home = $this->ask('Redirect users here after login', '/dashboard');

        // ── 6. User model stub ────────────────────────────────────────────────
        $publishUser = $this->confirm(
            'Publish User model stub (adds HasPasskeys trait to app/Models/User.php)?',
            true,
        );

        // ── Publish assets ────────────────────────────────────────────────────
        $this->newLine();
        $this->info('Installing...');

        $this->callSilent('vendor:publish', ['--tag' => 'oxalis-assets', '--force' => true]);
        $this->line('  <fg=green>✓</> Assets published → public/vendor/oxalis/');

        $this->callSilent('vendor:publish', ['--tag' => 'oxalis-migrations', '--force' => $this->option('force')]);
        $this->line('  <fg=green>✓</> Migrations published');

        $this->callSilent('vendor:publish', ['--tag' => 'oxalis-views', '--force' => $this->option('force')]);
        $this->line('  <fg=green>✓</> Views published → resources/views/vendor/oxalis/');

        if ($publishUser) {
            $this->callSilent('vendor:publish', ['--tag' => 'oxalis-user', '--force' => true]);
            $this->line('  <fg=green>✓</> app/Models/User.php published');
        }

        // ── Write .env ────────────────────────────────────────────────────────
        $this->appendEnv($enabledKeys, $smartDispatch, $googleEnabled, $githubEnabled, $googleId, $googleSecret, $githubId, $githubSecret, $prefix, $home);
        $this->line('  <fg=green>✓</> .env updated');

        // ── Replace default auth routes (always automatic) ───────────────────
        $this->addAuthRedirects();
        $this->line('  <fg=green>✓</> /login, /register, and /logout now handled by oxalis');

        // ── Migrate ───────────────────────────────────────────────────────────
        if ($this->confirm('Run php artisan migrate now?', true)) {
            $this->call('migrate');
        }

        // ── Summary ───────────────────────────────────────────────────────────
        $this->newLine();
        $this->line('  <fg=green;options=bold>oxalis installed!</>');
        $this->newLine();

        // Use the variable captured from the wizard — config() may still be stale
        // if the PHP process cached the config before appendEnv() wrote .env.
        $p    = $prefix;
        $home = $smartDispatch ? "{$p}/start" : "{$p}/login";

        $this->line('  <fg=cyan;options=bold>User-facing routes:</>');
        $this->table(
            ['Route', 'Who sees it'],
            [
                [url($home),                  $smartDispatch ? 'Login (Smart Dispatch)' : 'Login page — all visitors'],
                [url("{$p}/register"),         'Registration — all visitors'],
                [url("{$p}/account"),          'Account settings — logged-in users'],
                [url("{$p}/account/sessions"), 'Active sessions — logged-in users'],
                [url("{$p}/passkeys/enroll"),  'Add passkey — logged-in users'],
                [url("{$p}/totp/setup"),       'Set up 2FA — logged-in users'],
            ],
        );

        $this->newLine();
        $this->line('  <fg=yellow;options=bold>⚠  Admin-only routes (add OXALIS_ADMIN=true to .env first):</>');
        $this->table(
            ['Route', 'What it does'],
            [
                [url("{$p}/admin"),          'Dashboard — users, events, lockouts'],
                [url("{$p}/admin/invites"),  'Invite code management'],
                [url("{$p}/admin/webhooks"), 'Webhook configuration'],
                [url("{$p}/stats"),          'Auth analytics'],
            ],
        );

        $this->newLine();
        $this->line('  <fg=cyan;options=bold>Passkeys:</>');
        $this->line('  1. Register at '.url("{$p}/register"));
        $this->line('  2. Enroll a passkey at '.url("{$p}/passkeys/enroll").' (required before passkey login works)');
        $this->line('  3. Diagnose config: '.url("{$p}/health/passkeys"));
        $this->newLine();
        $this->line('  <fg=yellow>Admin routes require a separate admin password set on first visit.</>');
        $this->line('  <fg=yellow>Regular users are blocked from /admin/* even if they know the URL.</>');
        $this->newLine();
        $this->comment('Show admin links in Blade only when admin is signed in:');
        $this->line('    <fg=white>@oxalisAdmin</> ... <fg=white>@endOxalisAdmin</>');
        $this->newLine();
        $this->comment('Middleware shortcuts:');
        $this->comment('  ->middleware("oxalis")          require user login');
        $this->comment('  ->middleware("oxalis.step-up")  require re-verification');
        $this->newLine();
        $this->callSilent('optimize:clear');
        $this->line('  <fg=green>✓</> Application cache cleared');

        return self::SUCCESS;
    }

    private function appendEnv(
        array   $enabled,
        bool    $smartDispatch,
        bool    $googleEnabled,
        bool    $githubEnabled,
        ?string $googleId,
        ?string $googleSecret,
        ?string $githubId,
        ?string $githubSecret,
        string  $prefix = 'oxalis',
        string  $home   = '/dashboard',
    ): void {
        $appUrl = config('app.url', 'http://localhost');
        $host   = parse_url($appUrl, PHP_URL_HOST) ?: 'localhost';
        $name   = config('app.name', 'App');
        $origins = WebAuthnConfig::originsCsv($appUrl, $this->laravel->make('request'));

        $flag = fn(string $key) => in_array($key, $enabled) ? 'true' : 'false';

        $lines = [
            '',
            '# Oxalis',
            "OXALIS_PREFIX={$prefix}",
            "OXALIS_HOME={$home}",
            "OXALIS_RP_ID={$host}",
            "OXALIS_RP_NAME=\"{$name}\"",
            "OXALIS_ORIGINS={$origins}",
            '',
            'OXALIS_ENABLE_PASSKEY='    . $flag('passkey'),
            'OXALIS_ENABLE_MAGIC_LINK=' . $flag('magic_link'),
            'OXALIS_ENABLE_EMAIL_OTP='  . $flag('email_otp'),
            'OXALIS_ENABLE_TOTP='       . $flag('totp'),
            'OXALIS_ENABLE_PASSWORD='   . $flag('password'),
            'OXALIS_ENABLE_SOCIAL='     . $flag('social'),
            'OXALIS_SMART_DISPATCH='    . ($smartDispatch ? 'true' : 'false'),
        ];

        if ($googleEnabled) {
            $lines = array_merge($lines, [
                '', 'OXALIS_GOOGLE_ENABLED=true',
                "GOOGLE_CLIENT_ID={$googleId}",
                "GOOGLE_CLIENT_SECRET={$googleSecret}",
                'GOOGLE_REDIRECT_URI=' . url("{$prefix}/social/google/callback"),
            ]);
        }

        if ($githubEnabled) {
            $lines = array_merge($lines, [
                '', 'OXALIS_GITHUB_ENABLED=true',
                "GITHUB_CLIENT_ID={$githubId}",
                "GITHUB_CLIENT_SECRET={$githubSecret}",
                'GITHUB_REDIRECT_URI=' . url("{$prefix}/social/github/callback"),
            ]);
        }

        $envPath = base_path('.env');

        // Fall back to copying .env.example if .env doesn't exist yet
        if (!File::exists($envPath)) {
            $example = base_path('.env.example');
            if (File::exists($example)) {
                File::copy($example, $envPath);
            } else {
                File::put($envPath, '');
            }
        }

        $existing = File::get($envPath);

        foreach ($lines as $line) {
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            $key = explode('=', $line)[0];

            // Update existing key in-place, or append if not found
            if (str_contains($existing, $key . '=')) {
                $existing = preg_replace('/^' . preg_quote($key, '/') . '=.*/m', $line, $existing);
            } else {
                $existing .= PHP_EOL . $line;
            }
        }

        File::put($envPath, $existing);
    }

    private function addAuthRedirects(): void
    {
        $webRoutes = base_path('routes/web.php');
        if (!File::exists($webRoutes)) {
            return;
        }

        $content = File::get($webRoutes);

        // 1. Comment out: require __DIR__.'/auth.php';  (Breeze / Livewire starters)
        $content = preg_replace(
            '/^(require\s+__DIR__\s*\.\s*[\'"]\/auth\.php[\'"]\s*;)/m',
            '// [oxalis] $1',
            $content
        );

        // 2. Comment out: Auth::routes();  (Laravel UI / older starters)
        $content = preg_replace(
            '/^(\s*Auth::routes\s*\(.*?\)\s*;)/m',
            '// [oxalis] $1',
            $content
        );

        // 3. Comment out inline auth route definitions that apps define directly in web.php.
        //    These conflict with the redirect shim because Laravel picks the first-registered
        //    route for URL matching — so any old Route::get('/login', ...) before the shim
        //    keeps serving the old page even after the shim is appended.
        $inlinePatterns = [
            // Single-line GET/POST routes for /login, /register, /logout, /forgot-password, /reset-password
            '/^(\s*Route::(get|post|any)\s*\(\s*[\'"]\/login[\'"](?!.*\/oxalis).*\);?\s*)$/m',
            '/^(\s*Route::(get|post|any)\s*\(\s*[\'"]\/register[\'"](?!.*\/oxalis).*\);?\s*)$/m',
            '/^(\s*Route::(get|post|any|delete)\s*\(\s*[\'"]\/logout[\'"](?!.*\/oxalis).*\);?\s*)$/m',
            '/^(\s*Route::match\s*\(\s*\[.*\]\s*,\s*[\'"]\/logout[\'"](?!.*\/oxalis).*\);?\s*)$/m',
            '/^(\s*Route::(get|post|any)\s*\(\s*[\'"]\/forgot-password[\'"](?!.*\/oxalis).*\);?\s*)$/m',
            '/^(\s*Route::(get|post|any)\s*\(\s*[\'"]\/reset-password[\'"](?!.*\/oxalis).*\);?\s*)$/m',
        ];

        foreach ($inlinePatterns as $pattern) {
            $content = preg_replace($pattern, '// [oxalis] $1', $content);
        }

        // 4. Append the oxalis redirect shim once
        if (!str_contains($content, 'oxalis — redirect /login and /register')) {
            $p = config('oxalis.routes.prefix', 'oxalis');
            $content .= PHP_EOL . implode(PHP_EOL, [
                '',
                '// oxalis — redirect /login and /register to oxalis routes',
                "Route::redirect('/login',          '/{$p}/login')->name('login');",
                "Route::redirect('/register',       '/{$p}/register')->name('register');",
                "Route::redirect('/password/reset', '/{$p}/forgot-password');",
                // Handles both GET bookmarks and POST form submissions (Breeze / Laravel UI).
                // Actually logs the user out so the session is invalidated.
                "Route::match(['get','post'], '/logout', function() { \\Illuminate\\Support\\Facades\\Auth::logout(); request()->session()->invalidate(); request()->session()->regenerateToken(); return redirect('/{$p}/login'); })->name('logout');",
                '',
            ]);
        }

        File::put($webRoutes, $content);
    }
}
