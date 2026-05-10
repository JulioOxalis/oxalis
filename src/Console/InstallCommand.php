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

        // ── 5. Replace Laravel default auth ──────────────────────────────────
        $this->newLine();
        $replaceAuth = $this->confirm(
            'Add redirects for /login and /register → oxalis routes?',
            true,
        );

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
        $this->appendEnv($enabledKeys, $smartDispatch, $googleEnabled, $githubEnabled, $googleId, $googleSecret, $githubId, $githubSecret);
        $this->line('  <fg=green>✓</> .env updated');

        // ── Replace default auth ──────────────────────────────────────────────
        if ($replaceAuth) {
            $this->addAuthRedirects();
            $this->line('  <fg=green>✓</> Default auth routes redirected to oxalis');
        }

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
    ): void {
        $host = parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST) ?: 'localhost';
        $name = config('app.name', 'App');
        $url  = config('app.url', 'http://localhost');

        $flag = fn(string $key) => in_array($key, $enabled) ? 'true' : 'false';

        $lines = [
            '',
            '# Oxalis',
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
        if (File::exists($envPath)) {
            File::append($envPath, PHP_EOL . implode(PHP_EOL, $lines) . PHP_EOL);
        }
    }

    private function addAuthRedirects(): void
    {
        $webRoutes = base_path('routes/web.php');
        if (!File::exists($webRoutes)) {
            return;
        }

        $shim = <<<'PHP'


// oxalis — redirect Laravel default auth URLs to oxalis
Route::redirect('/login',          '/oxalis/login')->name('login');
Route::redirect('/register',       '/oxalis/register');
Route::redirect('/password/reset', '/oxalis/forgot-password');
PHP;

        $content = File::get($webRoutes);
        if (!str_contains($content, 'oxalis — redirect Laravel default auth URLs')) {
            File::append($webRoutes, $shim . PHP_EOL);
        }

        // Wrap routes/auth.php content in a block comment if it exists (Breeze / UI)
        $authFile = base_path('routes/auth.php');
        if (File::exists($authFile)) {
            $existing = File::get($authFile);
            if (!str_starts_with(ltrim($existing), '// [oxalis]')) {
                File::put($authFile, '// [oxalis] Original routes commented out — using oxalis instead' . PHP_EOL . '/*' . PHP_EOL . $existing . PHP_EOL . '*/');
            }
        }
    }
}
