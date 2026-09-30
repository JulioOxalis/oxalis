<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as AuthenticatableUser;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Oxalis\EmailOtp\OtpService;
use Oxalis\MagicLink\MagicLinkService;
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
