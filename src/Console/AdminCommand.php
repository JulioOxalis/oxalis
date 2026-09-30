<?php
namespace Oxalis\Console;

use Oxalis\Models\AdminCredential;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminCommand extends Command
{
    protected $signature   = 'oxalis:admin {action? : reset-password|reset|status}';
    protected $description = 'Manage the Oxalis admin panel credentials';

    public function handle(): int
    {
        $action = $this->argument('action') ?: $this->choice(
            'What do you want to do?',
            ['reset-password', 'reset (clear all admin credentials)', 'status'],
            0
        );

        // normalise the choice label back to key
        $action = str_starts_with($action, 'reset (') ? 'reset' : $action;

        return match ($action) {
            'reset-password' => $this->resetPassword(),
            'reset'          => $this->resetAll(),
            'status'         => $this->showStatus(),
            default          => $this->error("Unknown action. Use: reset-password, reset, or status.") ?? self::FAILURE,
        };
    }

    private function resetPassword(): int
    {
        if (!AdminCredential::isSetup()) {
            $this->warn('No admin credentials found. Visit /oxalis/admin/setup to create them.');
            return self::FAILURE;
        }

        $password = $this->secret('New admin password (min 12 chars)');

        if (strlen($password) < 12) {
            $this->error('Password must be at least 12 characters.');
            return self::FAILURE;
        }

        $confirm = $this->secret('Confirm new password');

        if ($password !== $confirm) {
            $this->error('Passwords do not match.');
            return self::FAILURE;
        }

        $cred = AdminCredential::first();
        $cred->update([
            'password_hash'   => Hash::make($password),
            'session_version' => Str::random(32),
        ]);

        $this->newLine();
        $this->line('  <fg=green>✓</> Admin password updated. All existing sessions have been invalidated.');
        $this->line('  Visit <comment>/oxalis/admin/login</comment> to sign in with the new password.');
        $this->newLine();

        return self::SUCCESS;
    }

    private function resetAll(): int
    {
        if (!AdminCredential::isSetup()) {
            $this->warn('No admin credentials found — nothing to reset.');
            return self::SUCCESS;
        }

        if (!$this->confirm('This will delete all admin credentials. You will need to set up a new password at /oxalis/admin. Continue?', false)) {
            $this->line('Aborted.');
            return self::SUCCESS;
        }

        AdminCredential::truncate();

        $this->newLine();
        $this->line('  <fg=green>✓</> Admin credentials cleared.');
        $this->line('  Visit <comment>/oxalis/admin</comment> — you will be redirected to the setup page.');
        $this->newLine();

        return self::SUCCESS;
    }

    private function showStatus(): int
    {
        if (!AdminCredential::isSetup()) {
            $this->line('  Admin panel: <fg=yellow>not configured</>');
            $this->line('  Run <comment>php artisan oxalis:admin reset-password</comment> or visit /oxalis/admin to set up.');
            return self::SUCCESS;
        }

        $cred = AdminCredential::first();

        $this->newLine();
        $this->table(['Field', 'Value'], [
            ['Admin panel',   config('oxalis.admin.enabled', false) ? '<fg=green>enabled</>' : '<fg=red>disabled (set OXALIS_ADMIN=true)</>'],
            ['TOTP',          $cred->hasTotpEnabled() ? '<fg=green>enabled</>' : '<fg=yellow>not enabled</>'],
            ['Last login',    $cred->last_login_at?->diffForHumans() ?? 'never'],
            ['Last login IP', $cred->last_login_ip ?? '—'],
        ]);
        $this->newLine();

        return self::SUCCESS;
    }
}
