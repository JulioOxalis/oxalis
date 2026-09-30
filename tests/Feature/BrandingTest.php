<?php

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
