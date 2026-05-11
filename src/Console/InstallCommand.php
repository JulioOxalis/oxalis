<?php
namespace Oxalis\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

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
            false,
        );

        // ── Publish assets ────────────────────────────────────────────────────
        $this->newLine();
        $this->info('Installing...');

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
        $this->line('  <fg=green>✓</> /login and /register now redirect to oxalis');

        // ── Migrate ───────────────────────────────────────────────────────────
        if ($this->confirm('Run php artisan migrate now?', true)) {
            $this->call('migrate');
        }

        // ── Summary ───────────────────────────────────────────────────────────
        $this->newLine();
        $this->line('  <fg=green;options=bold>oxalis installed!</>');
        $this->newLine();

        $p    = config('oxalis.routes.prefix', 'oxalis');
        $home = $smartDispatch ? "{$p}/start" : "{$p}/login";

        $this->table(
            ['Route', 'What it does'],
            [
                [url($home),                  $smartDispatch ? 'Login (Smart Dispatch — one field)' : 'Login page'],
                [url("{$p}/register"),         'Register'],
                [url("{$p}/passkeys/manage"),  'Manage passkeys'],
                [url("{$p}/totp/setup"),       'Set up authenticator app'],
                [url("{$p}/step-up"),          'Step-up auth prompt'],
            ],
        );

        $this->newLine();
        $this->comment('Add to any route: ->middleware("oxalis") to require login');
        $this->comment('Add to any route: ->middleware("oxalis.step-up") to require re-verification');
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
        $host = parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST) ?: 'localhost';
        $name = config('app.name', 'App');
        $url  = config('app.url', 'http://localhost');

        $flag = fn(string $key) => in_array($key, $enabled) ? 'true' : 'false';

        $lines = [
            '',
            '# Oxalis',
            "OXALIS_PREFIX={$prefix}",
            "OXALIS_HOME={$home}",
            "OXALIS_RP_ID={$host}",
            "OXALIS_RP_NAME=\"{$name}\"",
            "OXALIS_ORIGINS={$url}",
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
                'GOOGLE_REDIRECT_URI=' . url('oxalis/social/google/callback'),
            ]);
        }

        if ($githubEnabled) {
            $lines = array_merge($lines, [
                '', 'OXALIS_GITHUB_ENABLED=true',
                "GITHUB_CLIENT_ID={$githubId}",
                "GITHUB_CLIENT_SECRET={$githubSecret}",
                'GITHUB_REDIRECT_URI=' . url('oxalis/social/github/callback'),
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

        // 3. Append the oxalis redirect shim once
        if (!str_contains($content, 'oxalis — redirect /login and /register')) {
            $p = config('oxalis.routes.prefix', 'oxalis');
            $content .= PHP_EOL . implode(PHP_EOL, [
                '',
                '// oxalis — redirect /login and /register to oxalis routes',
                "Route::redirect('/login',          '/{$p}/login')->name('login');",
                "Route::redirect('/register',       '/{$p}/register')->name('register');",
                "Route::redirect('/password/reset', '/{$p}/forgot-password');",
                '',
            ]);
        }

        File::put($webRoutes, $content);
    }
}
