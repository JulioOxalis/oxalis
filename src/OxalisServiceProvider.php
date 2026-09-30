<?php

namespace Oxalis;

use Illuminate\Support\ServiceProvider;
use Oxalis\Http\Middleware\SecurityHeaders;

/**
 * Oxalis delegates authentication ceremonies to Laravel Fortify and Laravel
 * Passkeys. It no longer registers a parallel set of auth routes.
 */
class OxalisServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/oxalis.php', 'oxalis');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->bound('router')) {
            $this->app['router']->aliasMiddleware('oxalis.security.headers', SecurityHeaders::class);
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/oxalis.php' => config_path('oxalis.php'),
            ], 'oxalis-config');

            $this->publishesMigrations([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'oxalis-migrations');

            $this->publishes([
                __DIR__.'/../resources/css/auth.css' => public_path('vendor/oxalis/auth.css'),
            ], 'oxalis-assets');

            $this->publishes([
                __DIR__.'/../resources/views/auth' => resource_path('views/auth'),
                __DIR__.'/../resources/views/components/auth' => resource_path('views/components/auth'),
            ], 'oxalis-views');
        }
    }
}
