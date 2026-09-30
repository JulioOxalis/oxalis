<?php

use Illuminate\Http\Request;
use Oxalis\Support\WebAuthnConfig;

it('suggests localhost origin variants for dev', function () {
    $origins = WebAuthnConfig::suggestedOrigins('http://localhost');

    expect($origins)
        ->toContain('http://localhost')
        ->toContain('http://127.0.0.1')
        ->toContain('http://localhost:8000');
});

it('detects origin mismatch against request', function () {
    $request = Request::create('http://localhost:8000/oxalis/login', 'GET');
    $configured = ['http://localhost'];

    expect(WebAuthnConfig::originMatchesRequest($configured, $request))->toBeFalse();
    expect(WebAuthnConfig::originMatchesRequest(
        array_merge($configured, ['http://localhost:8000']),
        $request
    ))->toBeTrue();
});

it('matches rp id to browser host', function () {
    $request = Request::create('http://localhost:8000', 'GET');

    expect(WebAuthnConfig::rpIdMatchesRequest('localhost', $request))->toBeTrue();
    expect(WebAuthnConfig::rpIdMatchesRequest('example.com', $request))->toBeFalse();
});
