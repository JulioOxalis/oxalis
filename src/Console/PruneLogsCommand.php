<?php
namespace Oxalis\Console;

use Oxalis\Models\AuthEvent;
use Illuminate\Console\Command;

class PruneLogsCommand extends Command
{
    protected $signature   = 'oxalis:prune-logs {--days= : Override the retention window (days)}';
    protected $description = 'Delete Oxalis auth events older than the configured retention window';

    public function handle(): int
    {
        $days = $this->option('days') !== null
            ? (int) $this->option('days')
            : (int) config('oxalis.log_retention_days', 365);

        if ($days <= 0) {
            $this->line('  <fg=yellow>Retention window is 0 — nothing deleted (keep forever mode).</>');
            return self::SUCCESS;
        }

        $deleted = AuthEvent::where('created_at', '<', now()->subDays($days))->delete();

        $this->line("  <fg=green>✓</> Deleted {$deleted} auth event(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}
