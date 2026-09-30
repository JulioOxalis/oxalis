<?php

use Illuminate\Support\Facades\Blade;
use Oxalis\Support\Branding;

it('normalizes public logo paths to asset urls', function () {
    config(['oxalis.brand.logo_url' => '/img/logo.svg']);
    expect(Branding::logoUrl())->toBe(asset('img/logo.svg'));

    config(['oxalis.brand.logo_url' => 'img/logo.svg']);
    expect(Branding::logoUrl())->toBe(asset('img/logo.svg'));

    config(['oxalis.brand.logo_url' => 'public/img/logo.svg']);
    expect(Branding::logoUrl())->toBe(asset('img/logo.svg'));
});

it('keeps full logo urls unchanged', function () {
    config(['oxalis.brand.logo_url' => 'https://cdn.example.test/logo.svg']);

    expect(Branding::logoUrl())->toBe('https://cdn.example.test/logo.svg');
});

it('normalizes layout and card image options', function () {
    config([
        'oxalis.layout' => ' Split ',
        'oxalis.brand.card_image_position' => 'sideways',
        'oxalis.brand.card_image_height' => 999,
    ]);

    expect(Branding::layout())->toBe('split');
    expect(Branding::cardImagePosition())->toBe('top');
    expect(Branding::cardImageHeight())->toBe(360);
});

it('keeps the optional auth security note muted and off by default', function () {
    config(['oxalis.brand.security_strip' => false]);

    expect(Blade::render('@include("oxalis::partials.trust-strip")'))->not->toContain('Protected by Oxalis');

    config(['oxalis.brand.security_strip' => true]);

    $html = Blade::render('@include("oxalis::partials.trust-strip")');

    expect($html)
        ->toContain('ox-security-note')
        ->toContain('Protected by Oxalis security controls')
        ->not->toContain('Passkey ready')
        ->not->toContain('2FA supported')
        ->not->toContain('Rate limited');
});

it('renders labeled login method tiles', function () {
    $this->get('/oxalis/login')
        ->assertOk()
        ->assertSee('ox-method-label', false)
        ->assertSee('Passkey')
        ->assertSee('Password')
        ->assertDontSee('ox-auth-theme-switcher', false)
        ->assertDontSee('Secured by Oxalis');
});
