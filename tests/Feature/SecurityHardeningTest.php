<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as AuthenticatableUser;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Oxalis\EmailOtp\OtpService;
use Oxalis\MagicLink\MagicLinkService;
use Oxalis\Models\AdminCredential;
use Oxalis\Models\MagicLink;
use Oxalis\Models\OtpChallenge;

it('blocks disabled auth methods server side', function () {
    config()->set('oxalis.methods.password', false);

    $this->post('/oxalis/password', [
        'email' => 'user@example.com',
        'password' => 'secret',
    ])->assertNotFound();
});

it('enforces passkey only mode against magic link endpoints', function () {
    config()->set('oxalis.passkey_only', true);
    config()->set('oxalis.methods.magic_link', true);

    $this->post('/oxalis/magic-link/send', [
        'email' => 'user@example.com',
    ])->assertNotFound();
});

it('emits content security policy headers on oxalis responses', function () {
    $this->get('/oxalis/docs')
        ->assertOk()
        ->assertHeader('Content-Security-Policy');
});

it('accepts a correct otp on the configured final attempt', function () {
    Schema::dropIfExists('oxalis_otp_challenges');
    Schema::dropIfExists('oxalis_lockouts');

    Schema::create('oxalis_otp_challenges', function (Blueprint $table) {
        $table->id();
        $table->string('user_id')->index();
        $table->string('token', 40)->unique();
        $table->string('code_hash');
        $table->string('status', 20)->default('pending')->index();
        $table->unsignedInteger('attempts')->default(0);
        $table->unsignedInteger('max_attempts')->default(5);
        $table->timestamp('expires_at')->index();
        $table->timestamps();
    });

    Schema::create('oxalis_lockouts', function (Blueprint $table) {
        $table->id();
        $table->string('key', 128)->unique();
        $table->unsignedInteger('attempts')->default(0);
        $table->timestamp('locked_until')->nullable();
        $table->timestamps();
    });

    OtpChallenge::create([
        'user_id' => '507f1f77bcf86cd799439011',
        'token' => 'otp-token',
        'code_hash' => bcrypt('123456'),
        'status' => 'pending',
        'attempts' => 4,
        'max_attempts' => 5,
        'expires_at' => now()->addMinutes(5),
    ]);

    expect(app(OtpService::class)->verify('otp-token', '123456', '127.0.0.1'))->toBeTrue()
        ->and(OtpChallenge::where('token', 'otp-token')->first()->status)->toBe('approved');
});

it('stores new magic link tokens hashed at rest', function () {
    $this->app->detectEnvironment(fn () => 'local');
    config()->set('mail.default', 'array');

    Schema::dropIfExists('oxalis_magic_links');

    Schema::create('oxalis_magic_links', function (Blueprint $table) {
        $table->id();
        $table->string('user_id')->index();
        $table->string('token', 64)->unique();
        $table->timestamp('expires_at')->index();
        $table->timestamp('used_at')->nullable();
        $table->string('ip_address', 45)->nullable();
        $table->timestamps();
    });

    $user = new class extends AuthenticatableUser {
        protected $guarded = [];
    };
    $user->forceFill([
        'id' => '507f1f77bcf86cd799439011',
        'email' => 'user@example.com',
    ]);

    app(MagicLinkService::class)->send($user, '127.0.0.1');

    $token = Str::afterLast(session('oxalis_dev_magic_link'), '/');
    $stored = MagicLink::firstOrFail()->token;

    expect($stored)->toBe(hash('sha256', $token))
        ->and($stored)->not->toBe($token);
});

it('renders the documented oxalis user menu blade alias', function () {
    $user = new class extends AuthenticatableUser {
        protected $guarded = [];
    };
    $user->forceFill([
        'id' => '507f1f77bcf86cd799439011',
        'name' => 'Jane Developer',
        'email' => 'jane@example.com',
    ]);

    $this->actingAs($user);

    $html = Blade::render('<x-oxalis-user-menu />');

    expect($html)
        ->toContain('Jane Developer')
        ->toContain('Account settings')
        ->toContain('Sign out')
        ->toContain('<details class="ox-user-menu">');
});

it('supports the documented oxalis admin blade directive closing tag', function () {
    expect(trim(Blade::render('@oxalisAdmin Admin panel @endOxalisAdmin')))->toBe('');

    session(['oxalis_admin_authenticated' => true]);

    expect(Blade::render('@oxalisAdmin Admin panel @endOxalisAdmin'))->toContain('Admin panel');
});

it('recovers admin user filters from poisoned cached aggregate data', function () {
    config()->set('oxalis.admin.enabled', true);
    config()->set('oxalis.user_model', AuthenticatableUser::class);

    foreach ([
        'users',
        'oxalis_admin_credentials',
        'oxalis_passkeys',
        'oxalis_totp_secrets',
        'oxalis_auth_events',
        'oxalis_lockouts',
    ] as $table) {
        Schema::dropIfExists($table);
    }

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->unique();
        $table->string('password')->nullable();
        $table->timestamps();
    });

    Schema::create('oxalis_admin_credentials', function (Blueprint $table) {
        $table->id();
        $table->string('password_hash');
        $table->string('totp_secret')->nullable();
        $table->timestamp('totp_confirmed_at')->nullable();
        $table->string('session_version', 32)->default('test-version');
        $table->timestamp('last_login_at')->nullable();
        $table->string('last_login_ip', 45)->nullable();
        $table->timestamps();
    });

    Schema::create('oxalis_passkeys', function (Blueprint $table) {
        $table->id();
        $table->string('user_id')->index();
        $table->string('credential_id')->unique();
        $table->timestamps();
    });

    Schema::create('oxalis_totp_secrets', function (Blueprint $table) {
        $table->id();
        $table->string('user_id')->index();
        $table->text('secret')->nullable();
        $table->timestamp('confirmed_at')->nullable();
        $table->timestamps();
    });

    Schema::create('oxalis_auth_events', function (Blueprint $table) {
        $table->id();
        $table->string('user_id')->index();
        $table->string('event', 50)->default('login');
        $table->string('method', 50)->nullable();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->string('status', 20)->default('success');
        $table->timestamps();
    });

    Schema::create('oxalis_lockouts', function (Blueprint $table) {
        $table->id();
        $table->string('key', 128)->unique();
        $table->unsignedInteger('attempts')->default(0);
        $table->timestamp('locked_until')->nullable();
        $table->timestamps();
    });

    $user = new AuthenticatableUser();
    $user->forceFill([
        'name' => 'TOTP User',
        'email' => 'totp@example.com',
        'password' => Hash::make('password'),
    ]);
    $user->save();

    AdminCredential::create([
        'password_hash' => Hash::make('admin-password'),
        'session_version' => 'test-version',
    ]);

    $poison = unserialize('O:8:"stdClass":0:{}', ['allowed_classes' => false]);
    Cache::put('oxalis_admin_totp_' . md5((string) $user->getAuthIdentifier()), $poison, 300);
    Cache::put('oxalis_admin_global', $poison, 60);

    $this->actingAs($user)
        ->withSession([
            'oxalis_admin_authenticated' => true,
            'oxalis_admin_at' => now()->timestamp,
            'oxalis_admin_version' => 'test-version',
        ])
        ->get('/oxalis/admin?filter=totp')
        ->assertOk();
});
