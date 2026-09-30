<?php
namespace Oxalis\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishThemeCommand extends Command
{
    protected $signature   = 'oxalis:theme:publish {--force : Overwrite existing theme.css}';
    protected $description = 'Publish the Oxalis custom-theme CSS stub to public/vendor/oxalis/theme.css';

    public function handle(): int
    {
        $dest = public_path('vendor/oxalis/theme.css');
        $src  = __DIR__.'/../../resources/css/theme-stub.css';

        if (File::exists($dest) && ! $this->option('force')) {
            $this->warn('theme.css already exists. Use --force to overwrite.');
            return self::SUCCESS;
        }

        File::ensureDirectoryExists(dirname($dest));
        File::copy($src, $dest);

        $this->info('Published: public/vendor/oxalis/theme.css');
        $this->line('');
        $this->line('Next steps:');
        $this->line('  1. Add <fg=yellow>OXALIS_THEME=custom</> to your .env');
        $this->line('  2. Edit <fg=cyan>public/vendor/oxalis/theme.css</> to set your brand colours');
        $this->line('  3. Optionally set <fg=yellow>OXALIS_PRIMARY_COLOR=#hexvalue</> to auto-derive hover/fill tokens');

        return self::SUCCESS;
    }
}
