<?php
namespace Oxalis;

use Oxalis\Auth\LoginHandler;
use Oxalis\Telemetry\TelemetryService;
use Oxalis\Console\InstallCommand;
use Oxalis\Console\LogCommand;
use Oxalis\Console\UninstallCommand;
use Oxalis\Console\UserCommand;
use Oxalis\EmailOtp\OtpService;
use Oxalis\EmailVerification\EmailVerificationService;
use Oxalis\Http\Middleware\OxalisAdminAuth;
use Oxalis\Http\Middleware\OxalisIpThrottle;
use Oxalis\Http\Middleware\OxalisSessionGuard;
use Oxalis\Webhooks\WebhookService;
use Oxalis\Http\Middleware\OxalisThrottle;
use Oxalis\Http\Middleware\RequireEmailVerified;
use Oxalis\Http\Middleware\RequireOxalis;
use Oxalis\Http\Middleware\RequireStepUp;
use Oxalis\Http\Middleware\ValidatePasskeySession;
use Oxalis\MagicLink\MagicLinkService;
use Oxalis\StepUp\StepUpService;
use Oxalis\Totp\TotpService;
use Oxalis\WebAuthn\WebAuthnService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class OxalisServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/oxalis.php', 'oxalis');

        $this->app->singleton(LoginHandler::class);
        $this->app->singleton(WebAuthnService::class);
        $this->app->singleton(OtpService::class);
        $this->app->singleton(MagicLinkService::class);
        $this->app->singleton(TotpService::class);
        $this->app->singleton(StepUpService::class);
        $this->app->singleton(OxalisManager::class);
        $this->app->singleton(EmailVerificationService::class);
        $this->app->singleton(WebhookService::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/oxalis.php' => config_path('oxalis.php'),
        ], 'oxalis-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'oxalis-migrations');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/oxalis'),
        ], 'oxalis-views');

        $this->publishes([
            __DIR__.'/../stubs/User.php' => app_path('Models/User.php'),
        ], 'oxalis-user');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'oxalis');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        Blade::componentNamespace('Oxalis\\View\\Components', 'oxalis');

        foreach (['google', 'github'] as $provider) {
            $cfg = config("oxalis.social.{$provider}");
            if ($cfg && ($cfg['enabled'] ?? false)) {
                config(["services.{$provider}" => [
                    'client_id'     => $cfg['client_id'],
                    'client_secret' => $cfg['client_secret'],
                    'redirect'      => $cfg['redirect'] ?? route("oxalis.social.callback", $provider),
                ]]);
            }
        }

        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make('router');
        $router->aliasMiddleware('oxalis',               RequireOxalis::class);
        $router->aliasMiddleware('oxalis.step-up',       RequireStepUp::class);
        $router->aliasMiddleware('oxalis.throttle',      OxalisThrottle::class);
        $router->aliasMiddleware('oxalis.ip',            OxalisIpThrottle::class);
        $router->aliasMiddleware('oxalis.verified',      RequireEmailVerified::class);
        $router->aliasMiddleware('oxalis.passkey-session', ValidatePasskeySession::class);
        $router->aliasMiddleware('oxalis.admin-auth',    OxalisAdminAuth::class);
        $router->aliasMiddleware('oxalis.session-guard', OxalisSessionGuard::class);

        if ($this->app->runningInConsole()) {
            $this->commands([InstallCommand::class, LogCommand::class, UninstallCommand::class, UserCommand::class]);
        }

        // Optional anonymous telemetry — fires at most once per 24h, opt-in only
        if (config('oxalis.telemetry', false)) {
            $this->app->booted(function () {
                try {
                    app(TelemetryService::class)->maybePing();
                } catch (\Throwable) {}
            });
        }
    }
}
