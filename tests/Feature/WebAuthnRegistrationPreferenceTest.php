<?php

use Oxalis\WebAuthn\WebAuthnService;
use Webauthn\AuthenticatorSelectionCriteria;

function oxalisWebAuthnPrivateMethod(string $method): ReflectionMethod
{
    $reflection = new ReflectionClass(WebAuthnService::class);
    $method = $reflection->getMethod($method);
    $method->setAccessible(true);

    return $method;
}

it('maps this-device passkeys to platform attachment without security-key hints', function () {
    config()->set('oxalis.passkey_hints', ['client-device', 'hybrid', 'security-key']);

    $service = app(WebAuthnService::class);
    $attachment = oxalisWebAuthnPrivateMethod('authenticatorAttachment')->invoke($service, 'platform');
    $hints = oxalisWebAuthnPrivateMethod('passkeyHints')->invoke($service, $attachment);

    expect($attachment)->toBe(AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_PLATFORM);
    expect($hints)->toBe(['client-device']);
});

it('maps phone or security key passkeys to external authenticator hints', function () {
    config()->set('oxalis.passkey_hints', ['client-device', 'hybrid', 'security-key']);

    $service = app(WebAuthnService::class);
    $attachment = oxalisWebAuthnPrivateMethod('authenticatorAttachment')->invoke($service, 'cross-platform');
    $hints = oxalisWebAuthnPrivateMethod('passkeyHints')->invoke($service, $attachment);

    expect($attachment)->toBe(AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_CROSS_PLATFORM);
    expect($hints)->toBe(['hybrid', 'security-key']);
});
