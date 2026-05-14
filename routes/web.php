<?php
use Oxalis\Http\Controllers\AccountController;
use Oxalis\Http\Controllers\AdminAuthController;
use Oxalis\Http\Controllers\AdminController;
use Oxalis\Http\Controllers\InviteController;
use Oxalis\Http\Controllers\SessionController;
use Oxalis\Http\Controllers\WebhookController;
use Oxalis\Http\Controllers\AuthController;
use Oxalis\Http\Controllers\DocsController;
use Oxalis\Http\Controllers\EmailChangeController;
use Oxalis\Http\Controllers\StatsController;
use Oxalis\Http\Controllers\TestHubController;
use Oxalis\Http\Controllers\EmailVerificationController;
use Oxalis\Http\Controllers\MagicLinkController;
use Oxalis\Http\Controllers\OtpController;
use Oxalis\Http\Controllers\PasswordController;
use Oxalis\Http\Controllers\PasswordResetController;
use Oxalis\Http\Controllers\PasskeyController;
use Oxalis\Http\Controllers\RecoveryCodeController;
use Oxalis\Http\Controllers\SecurityController;
use Oxalis\Http\Controllers\SmartDispatchController;
use Oxalis\Http\Controllers\SocialController;
use Oxalis\Http\Controllers\StepUpController;
use Oxalis\Http\Controllers\TotpController;
use Illuminate\Support\Facades\Route;

$prefix     = config('oxalis.routes.prefix', 'oxalis');
$middleware = config('oxalis.routes.middleware', ['web']);

Route::prefix($prefix)->middleware($middleware)->group(function () {

    // ── Documentation (always public) ────────────────────────────────────────
    Route::get('/docs', [DocsController::class, 'index'])->name('oxalis.docs');

    // ── Dev test hub (local only) ─────────────────────────────────────────────
    Route::get('/test', [TestHubController::class, 'index'])->name('oxalis.test');

    // ── Guest ─────────────────────────────────────────────────────────────────
    // All POST auth routes are rate-limited. GET routes (show forms) are not.
    Route::middleware('guest')->group(function () {

        // Smart dispatch (One-Field Auth)
        Route::get('/start',  [SmartDispatchController::class, 'show'])->name('oxalis.dispatch');
        Route::post('/start', [SmartDispatchController::class, 'dispatch'])
            ->middleware('oxalis.ip:30,1')
            ->name('oxalis.dispatch.check');

        // Standard login page
        Route::get('/login', [AuthController::class, 'showLogin'])->name('oxalis.login');

        // Registration — 3 steps
        Route::get('/register',            [AuthController::class, 'showRegister'])->name('oxalis.register');
        Route::post('/register',           [AuthController::class, 'register'])->middleware('oxalis.ip:5,5')->name('oxalis.register.submit');
        Route::get('/register/verify',     [AuthController::class, 'showRegisterVerify'])->name('oxalis.register.verify.show');
        Route::post('/register/verify',    [AuthController::class, 'registerVerify'])->middleware('oxalis.ip:10,5')->name('oxalis.register.verify');
        Route::get('/register/password',   [AuthController::class, 'showRegisterPassword'])->name('oxalis.register.password.show');
        Route::post('/register/password',  [AuthController::class, 'registerPassword'])->middleware('oxalis.ip:30,1')->name('oxalis.register.password');

        // Passkey login — begin is the probe point bots would hammer
        Route::post('/passkeys/login/begin',   [PasskeyController::class, 'beginAuthentication'])
            ->middleware('oxalis.ip:30,1')
            ->name('oxalis.passkeys.login.begin');
        Route::post('/passkeys/login/finish',  [PasskeyController::class, 'finishAuthentication'])
            ->middleware('oxalis.ip:30,1')
            ->name('oxalis.passkeys.login.finish');
        // Conditional UI (autofill passkey) — no email required
        Route::post('/passkeys/login/autofill-begin', [PasskeyController::class, 'beginAutofill'])
            ->middleware('oxalis.ip:30,1')
            ->name('oxalis.passkeys.login.autofill');

        // Email OTP — send is strictly limited to prevent email spam
        Route::post('/otp/send',   [OtpController::class, 'send'])
            ->middleware('oxalis.ip:5,5')
            ->name('oxalis.otp.send');
        Route::post('/otp/verify', [OtpController::class, 'verify'])
            ->middleware('oxalis.ip:30,1')
            ->name('oxalis.otp.verify');

        // Magic link — send is strictly limited
        Route::post('/magic-link/send',          [MagicLinkController::class, 'send'])
            ->middleware('oxalis.ip:5,5')
            ->name('oxalis.magic-link.send');
        Route::get('/magic-link/verify/{token}', [MagicLinkController::class, 'verify'])
            ->name('oxalis.magic-link.verify');

        // TOTP login
        Route::get('/totp/verify',  [TotpController::class, 'showVerify'])->name('oxalis.totp.verify.show');
        Route::post('/totp/verify', [TotpController::class, 'verify'])
            ->middleware('oxalis.ip:30,1')
            ->name('oxalis.totp.verify');

        // Recovery code
        Route::get('/recovery-code/verify',  [RecoveryCodeController::class, 'show'])->name('oxalis.recovery.show');
        Route::post('/recovery-code/verify', [RecoveryCodeController::class, 'verify'])
            ->middleware('oxalis.ip:30,1')
            ->name('oxalis.recovery.verify');

        // Password login — also uses DB-based lockout via oxalis.throttle
        Route::get('/password',  [PasswordController::class, 'showLogin'])->name('oxalis.password.login.show');
        Route::post('/password', [PasswordController::class, 'login'])
            ->middleware(['oxalis.ip:30,1', 'oxalis.throttle'])
            ->name('oxalis.password.login');

        // Password reset
        Route::get('/forgot-password',         [PasswordResetController::class, 'showForgot'])->name('oxalis.password.forgot');
        Route::post('/forgot-password',        [PasswordResetController::class, 'sendLink'])
            ->middleware('oxalis.ip:5,5')
            ->name('oxalis.password.forgot.send');
        Route::get('/reset-password/{token}',  [PasswordResetController::class, 'showReset'])->name('oxalis.password.reset.show');
        Route::post('/reset-password',         [PasswordResetController::class, 'reset'])
            ->middleware('oxalis.ip:30,1')
            ->name('oxalis.password.reset');

        // Social login
        Route::get('/social/{provider}',          [SocialController::class, 'redirect'])->name('oxalis.social.redirect');
        Route::get('/social/{provider}/callback', [SocialController::class, 'callback'])->name('oxalis.social.callback');

        // Email verification
        Route::get('/verify-email',         [EmailVerificationController::class, 'notice'])->name('oxalis.email.notice');
        Route::post('/verify-email/send',   [EmailVerificationController::class, 'send'])->name('oxalis.email.send');
        Route::get('/verify-email/{token}', [EmailVerificationController::class, 'verify'])->name('oxalis.email.verify');

        // Recovery code login
        Route::get('/recovery-code/verify',  [RecoveryCodeController::class, 'show'])->name('oxalis.recovery.show');
        Route::post('/recovery-code/verify', [RecoveryCodeController::class, 'verify'])->name('oxalis.recovery.verify');
    });

    // ── Authenticated ─────────────────────────────────────────────────────────
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('oxalis.logout');

        // Passkey management
        Route::get('/passkeys/enroll',           [PasskeyController::class, 'showEnroll'])->name('oxalis.passkeys.enroll');
        Route::get('/passkeys/manage',           [PasskeyController::class, 'showManage'])->name('oxalis.passkeys.manage');
        Route::post('/passkeys/register/begin',  [PasskeyController::class, 'beginRegistration'])->name('oxalis.passkeys.register.begin');
        Route::post('/passkeys/register/finish', [PasskeyController::class, 'finishRegistration'])->name('oxalis.passkeys.register.finish');
        Route::post('/passkeys/rename',          [PasskeyController::class, 'rename'])->name('oxalis.passkeys.rename');
        Route::post('/passkeys/delete',          [PasskeyController::class, 'delete'])->name('oxalis.passkeys.delete');

        // TOTP management
        Route::get('/totp/setup',    [TotpController::class, 'showSetup'])->name('oxalis.totp.setup');
        Route::post('/totp/confirm', [TotpController::class, 'confirmSetup'])->name('oxalis.totp.confirm');
        Route::get('/totp/manage',   [TotpController::class, 'showManage'])->name('oxalis.totp.manage');
        Route::post('/totp/disable', [TotpController::class, 'disable'])->name('oxalis.totp.disable');

        // Step-up
        Route::get('/step-up',          [StepUpController::class, 'prompt'])->name('oxalis.step-up.prompt');
        Route::post('/step-up/totp',    [StepUpController::class, 'verifyTotp'])->name('oxalis.step-up.totp');
        Route::post('/step-up/passkey', [StepUpController::class, 'verifyPasskey'])->name('oxalis.step-up.passkey');

        // Recovery code management
        Route::post('/recovery-code/regenerate', [RecoveryCodeController::class, 'regenerate'])->name('oxalis.recovery.regenerate');

        // Account (unified settings page)
        Route::get('/account',        [AccountController::class, 'index'])->name('oxalis.account');
        Route::post('/account/delete',[AccountController::class, 'deleteAccount'])->name('oxalis.account.delete');
        // Legacy alias kept for backwards compat
        Route::get('/account/security', [SecurityController::class, 'index'])->name('oxalis.account.security');

        // Email change flow
        Route::get('/account/email',         [EmailChangeController::class, 'show'])->name('oxalis.account.email.show');
        Route::post('/account/email',        [EmailChangeController::class, 'send'])->middleware('oxalis.ip:5,5')->name('oxalis.account.email.send');
        Route::get('/account/email/verify',  [EmailChangeController::class, 'showVerify'])->name('oxalis.account.email.verify.show');
        Route::post('/account/email/verify', [EmailChangeController::class, 'verify'])->middleware('oxalis.ip:10,5')->name('oxalis.account.email.verify');

        // Auth analytics — admin session required
        Route::get('/stats', [StatsController::class, 'index'])
            ->middleware('oxalis.admin-auth')
            ->name('oxalis.stats');

        // Active sessions
        Route::get('/account/sessions',          [SessionController::class, 'index'])->name('oxalis.sessions');
        Route::post('/account/sessions/revoke',  [SessionController::class, 'revoke'])->name('oxalis.sessions.revoke');
        Route::post('/account/sessions/revoke-all',[SessionController::class, 'revokeAll'])->name('oxalis.sessions.revoke-all');

        // Admin panel — protected by OxalisAdminAuth middleware
        Route::middleware('oxalis.admin-auth')->prefix('admin')->group(function () {
            Route::get('/',                    [AdminController::class,     'index'])           ->name('oxalis.admin');
            Route::get('/events',              [AdminController::class,     'events'])          ->name('oxalis.admin.events');
            Route::get('/lockouts',            [AdminController::class,     'lockouts'])        ->name('oxalis.admin.lockouts');
            Route::post('/lockouts/clear',     [AdminController::class,     'clearLockout'])    ->name('oxalis.admin.lockouts.clear');
            Route::post('/lockouts/clear-all', [AdminController::class,     'clearAllLockouts'])->name('oxalis.admin.lockouts.clear-all');
            Route::get('/change-password',     [AdminAuthController::class, 'showChangePassword'])->name('oxalis.admin.password');
            Route::post('/change-password',[AdminAuthController::class,'changePassword']) ->name('oxalis.admin.password.post');
            Route::post('/logout',        [AdminAuthController::class, 'logout'])         ->name('oxalis.admin.logout');
            // Invites
            Route::get('/invites',        [InviteController::class, 'index'])  ->name('oxalis.admin.invites');
            Route::post('/invites',       [InviteController::class, 'store'])  ->name('oxalis.admin.invites.store');
            Route::post('/invites/delete',[InviteController::class, 'destroy'])->name('oxalis.admin.invites.destroy');
            // Webhooks
            Route::get('/webhooks',         [WebhookController::class, 'index'])  ->name('oxalis.admin.webhooks');
            Route::post('/webhooks',        [WebhookController::class, 'store'])  ->name('oxalis.admin.webhooks.store');
            Route::post('/webhooks/toggle', [WebhookController::class, 'toggle']) ->name('oxalis.admin.webhooks.toggle');
            Route::post('/webhooks/delete', [WebhookController::class, 'destroy'])->name('oxalis.admin.webhooks.destroy');
        });
        // Admin login/setup (outside auth middleware — accessible without admin session)
        Route::get('/admin/setup',  [AdminAuthController::class, 'showSetup'])->middleware('oxalis.admin-auth')->name('oxalis.admin.setup');
        Route::post('/admin/setup', [AdminAuthController::class, 'setup'])    ->middleware('oxalis.admin-auth')->name('oxalis.admin.setup.post');
        Route::get('/admin/login',  [AdminAuthController::class, 'showLogin'])->middleware('oxalis.admin-auth')->name('oxalis.admin.login');
        Route::post('/admin/login', [AdminAuthController::class, 'login'])    ->middleware(['oxalis.admin-auth','oxalis.ip:5,5'])->name('oxalis.admin.login.post');
    });
});

// ── Replace default Laravel auth URLs ─────────────────────────────────────────
// Uses the configured prefix so custom prefixes work automatically.
Route::middleware(config('oxalis.routes.middleware', ['web']))->group(function () {
    $p = config('oxalis.routes.prefix', 'oxalis');
    Route::redirect('/login',          "/{$p}/login")->name('login');
    Route::redirect('/register',       "/{$p}/register")->name('register');
    Route::redirect('/password/reset', "/{$p}/forgot-password");
    // Handles both GET /logout (old bookmarks) and POST /logout (Breeze / Laravel UI forms).
    // Actually logs the user out so sessions are invalidated, not just redirected away.
    Route::match(['get', 'post'], '/logout', function () use ($p) {
        \Illuminate\Support\Facades\Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect("/{$p}/login");
    })->name('logout');
});
