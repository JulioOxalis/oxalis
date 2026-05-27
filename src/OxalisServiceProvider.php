<?php
namespace Oxalis;

use Oxalis\Auth\LoginHandler;
use Oxalis\Telemetry\TelemetryService;
use Oxalis\Console\AdminCommand;
use Oxalis\Console\InstallCommand;
use Oxalis\Console\LogCommand;
use Oxalis\Console\PruneLogsCommand;
use Oxalis\Console\PublishThemeCommand;
use Oxalis\Console\UninstallCommand;
use Oxalis\Console\UserCommand;
use Oxalis\EmailOtp\OtpService;
use Oxalis\EmailVerification\EmailVerificationService;
use Oxalis\Http\Middleware\OxalisAdminAuth;
use Oxalis\Http\Middleware\OxalisIpThrottle;
use Oxalis\Http\Middleware\OxalisSecurityHeaders;
use Oxalis\Http\Middleware\OxalisSessionGuard;
use Oxalis\QrLogin\QrLoginService;
use Oxalis\Security\BreachCheckService;
use Oxalis\Security\RiskService;
use Oxalis\Ultrasonic\UltrasonicService;
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
        $this->app->singleton(BreachCheckService::class);
        $this->app->singleton(RiskService::class);
        $this->app->singleton(QrLoginService::class);
        $this->app->singleton(UltrasonicService::class);
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

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/oxalis'),
        ], 'oxalis-assets');

        $this->publishes([
            __DIR__.'/../resources/css/theme-stub.css' => public_path('vendor/oxalis/theme.css'),
        ], 'oxalis-theme');

        $this->publishes([
            __DIR__.'/../resources/views/partials' => resource_path('views/vendor/oxalis/partials'),
        ], 'oxalis-partials');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'oxalis');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        Blade::componentNamespace('Oxalis\\View\\Components', 'oxalis');

        // @oxalisAdmin / @endOxalisAdmin — renders content only when admin is authenticated
        Blade::if('oxalisAdmin', fn() => session('oxalis_admin_authenticated') === true);

        // @oxalisUser — renders content only when a regular user is logged in
        Blade::if('oxalisUser', fn() => \Illuminate\Support\Facades\Auth::check());

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
        $router->aliasMiddleware('oxalis.session-guard',    OxalisSessionGuard::class);
        $router->aliasMiddleware('oxalis.security-headers', OxalisSecurityHeaders::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                AdminCommand::class,
                InstallCommand::class,
                LogCommand::class,
                PruneLogsCommand::class,
                PublishThemeCommand::class,
                UninstallCommand::class,
                UserCommand::class,
            ]);
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
