<?php
namespace Oxalis\Console;

use Illuminate\Console\Command;
use Oxalis\Models\AuthEvent;

class LogCommand extends Command
{
    protected $signature = 'oxalis:log
                            {--user= : Filter by user ID or email}
                            {--method= : Filter by method (passkey, password, etc)}
                            {--limit=20 : Number of records}';

    protected $description = 'View recent Oxalis authentication events';

    public function handle(): int
    {
        $query = AuthEvent::latest('created_at');

        if ($email = $this->option('user')) {
            $userModel = config('oxalis.user_model');
            $user = $userModel::where('email', $email)->orWhere('id', $email)->first();
            if ($user) {
                $query->where('user_id', $user->getAuthIdentifier());
            } else {
                $this->error("User not found: {$email}");
                return self::FAILURE;
            }
        }

        if ($method = $this->option('method')) {
            $query->where('method', 'LIKE', "%{$method}%");
        }

        $events = $query->limit((int) $this->option('limit'))->get();

        if ($events->isEmpty()) {
            $this->info('No auth events found.');
            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'User ID', 'Method', 'IP', 'Status', 'When'],
            $events->map(fn ($e) => [
                $e->id,
                $e->user_id,
                $e->method,
                $e->ip_address ?? '—',
                $e->status,
                $e->created_at->diffForHumans(),
            ])->toArray()
        );

        $this->newLine();
        $this->line("  Showing {$events->count()} events. Use --limit=N for more.");

        return self::SUCCESS;
    }
}
