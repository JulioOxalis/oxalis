<?php
namespace Oxalis\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UninstallCommand extends Command
{
    protected $signature   = 'oxalis:remove';
    protected $description = 'Remove Oxalis from this application';

    private const ENV_KEYS = [
        'OXALIS_RP_ID', 'OXALIS_RP_NAME', 'OXALIS_ORIGINS',
        'OXALIS_ENABLE_PASSKEY', 'OXALIS_ENABLE_MAGIC_LINK', 'OXALIS_ENABLE_EMAIL_OTP',
        'OXALIS_ENABLE_TOTP', 'OXALIS_ENABLE_PASSWORD', 'OXALIS_ENABLE_SOCIAL',
        'OXALIS_SMART_DISPATCH', 'OXALIS_LOGIN_NOTIFICATION', 'OXALIS_REQUIRE_ATTESTATION',
        'OXALIS_LOCKOUT_ATTEMPTS', 'OXALIS_LOCKOUT_MINUTES',
        'OXALIS_RATE_PER_MINUTE', 'OXALIS_SEND_PER_WINDOW', 'OXALIS_SEND_WINDOW_MIN',
        'OXALIS_GOOGLE_ENABLED', 'OXALIS_GITHUB_ENABLED',
        'OXALIS_DB_CONNECTION', 'OXALIS_TELEMETRY', 'OXALIS_TELEMETRY_ENDPOINT',
    ];

    public function handle(): int
    {
        $this->newLine();
        $this->warn('  This will remove Oxalis configuration from this project.');
        $this->newLine();

        if (!$this->confirm('Are you sure you want to remove Oxalis?', false)) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        // ── 1. Clean .env ─────────────────────────────────────────────────────
        $this->cleanEnv();
        $this->line('  <fg=green>✓</> Oxalis variables removed from .env');

        // ── 2. Remove config ──────────────────────────────────────────────────
        $config = config_path('oxalis.php');
        if (File::exists($config)) {
            File::delete($config);
            $this->line('  <fg=green>✓</> config/oxalis.php deleted');
        }

        // ── 3. Remove published views ─────────────────────────────────────────
        $views = resource_path('views/vendor/oxalis');
        if (File::isDirectory($views)) {
            if ($this->confirm('Delete published views in resources/views/vendor/oxalis/?', true)) {
                File::deleteDirectory($views);
                $this->line('  <fg=green>✓</> Published views deleted');
            }
        }

        // ── 4. Remove redirects from routes/web.php ───────────────────────────
        $this->cleanRoutes();
        $this->line('  <fg=green>✓</> Oxalis redirects removed from routes/web.php');

        // ── 5. Rollback migrations ────────────────────────────────────────────
        if ($this->confirm('Rollback Oxalis database tables? (This deletes all auth data)', false)) {
            $tables = [
                'oxalis_passkeys', 'oxalis_otp_challenges', 'oxalis_trusted_devices',
                'oxalis_auth_events', 'oxalis_magic_links', 'oxalis_totp_secrets',
                'oxalis_password_resets', 'oxalis_lockouts', 'oxalis_social_logins',
                'oxalis_email_verifications',
            ];

            foreach ($tables as $table) {
                try {
                    \Illuminate\Support\Facades\Schema::dropIfExists($table);
                } catch (\Throwable) {}
            }

            $this->line('  <fg=green>✓</> Oxalis tables dropped');
        }

        $this->newLine();
        $this->line('  <fg=green;options=bold>Oxalis removed.</> Run <comment>composer remove julio/oxalis</comment> to fully uninstall.');
        $this->newLine();

        return self::SUCCESS;
    }

    private function cleanEnv(): void
    {
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            return;
        }

        $lines = explode("\n", File::get($envPath));
        $cleaned = [];
        $skipBlank = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Remove the # Oxalis section comment
            if ($trimmed === '# Oxalis') {
                $skipBlank = true;
                continue;
            }

            // Remove any OXALIS_* key lines
            $key = explode('=', $trimmed)[0];
            if (in_array($key, self::ENV_KEYS)) {
                $skipBlank = true;
                continue;
            }

            // Remove the blank line that immediately follows removed lines
            if ($skipBlank && $trimmed === '') {
                $skipBlank = false;
                continue;
            }

            $skipBlank = false;
            $cleaned[] = $line;
        }

        File::put($envPath, implode("\n", $cleaned));
    }

    private function cleanRoutes(): void
    {
        $path = base_path('routes/web.php');
        if (!File::exists($path)) {
            return;
        }

        $content = File::get($path);

        // Remove the oxalis redirect block we added during install
        $content = preg_replace(
            '/\n*\/\/ oxalis.*?oxalis\/forgot-password\'\);\n?/s',
            '',
            $content
        );

        File::put($path, $content);
    }
}
