<?php
namespace Oxalis\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class UserCommand extends Command
{
    protected $signature   = 'oxalis:user {action? : create|list|delete}';
    protected $description = 'Manage Oxalis users (create / list / delete)';

    public function handle(): int
    {
        $action = $this->argument('action') ?: $this->choice(
            'What do you want to do?',
            ['create', 'list', 'delete'],
            0
        );

        return match ($action) {
            'create' => $this->createUser(),
            'list'   => $this->listUsers(),
            'delete' => $this->deleteUser(),
            default  => $this->error("Unknown action. Use: create, list, or delete.") ?? self::FAILURE,
        };
    }

    private function createUser(): int
    {
        $userModel = config('oxalis.user_model');

        $name  = $this->ask('Full name');
        $email = $this->ask('Email address');

        if ($userModel::where('email', $email)->exists()) {
            $this->error("A user with email {$email} already exists.");
            return self::FAILURE;
        }

        $password = $this->secret('Password (min 8 chars)');
        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');
            return self::FAILURE;
        }

        $user = $userModel::create([
            'name'              => $name,
            'email'             => $email,
            'password'          => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        $this->newLine();
        $this->line("  <fg=green>✓</> User created: {$user->email} (ID: {$user->getAuthIdentifier()})");
        $this->newLine();

        return self::SUCCESS;
    }

    private function listUsers(): int
    {
        $userModel = config('oxalis.user_model');
        $users = $userModel::latest()->take(100)->get();

        if ($users->isEmpty()) {
            $this->line('  No users found.');
            return self::SUCCESS;
        }

        $rows = $users->map(fn($u) => [
            $u->getAuthIdentifier(),
            $u->name ?? '—',
            $u->email,
            $u->created_at?->diffForHumans() ?? '—',
        ]);

        $this->table(['ID', 'Name', 'Email', 'Registered'], $rows);

        $this->line("  Showing {$users->count()} user(s).");
        $this->newLine();

        return self::SUCCESS;
    }

    private function deleteUser(): int
    {
        $userModel = config('oxalis.user_model');

        $email = $this->ask('Email address of the user to delete');
        $user  = $userModel::where('email', $email)->first();

        if (!$user) {
            $this->error("No user found with email: {$email}");
            return self::FAILURE;
        }

        $this->line("  Found: <fg=yellow>{$user->name}</> ({$user->email})");

        if (!$this->confirm('Are you sure you want to delete this user and all their data?', false)) {
            $this->line('  Aborted.');
            return self::SUCCESS;
        }

        $uid = $user->getAuthIdentifier();

        // Delete oxalis data
        foreach ([
            \Oxalis\Models\Passkey::class,
            \Oxalis\Models\TotpSecret::class,
            \Oxalis\Models\OtpChallenge::class,
            \Oxalis\Models\MagicLink::class,
            \Oxalis\Models\SocialLogin::class,
            \Oxalis\Models\AuthEvent::class,
            \Oxalis\Models\TotpTrustedDevice::class,
        ] as $model) {
            try { $model::where('user_id', $uid)->delete(); } catch (\Throwable) {}
        }

        $user->delete();

        $this->line("  <fg=green>✓</> User {$email} deleted.");
        $this->newLine();

        return self::SUCCESS;
    }
}
