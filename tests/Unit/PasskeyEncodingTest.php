<?php

use Oxalis\Support\PasskeyEncoding;

it('round-trips base64url credential ids', function () {
    $binary = random_bytes(32);
    $encoded = PasskeyEncoding::encode($binary);

    expect(PasskeyEncoding::decode($encoded))->toBe($binary);
});

it('normalizes client rawId to standard base64 storage', function () {
    $binary = random_bytes(16);
    $url = PasskeyEncoding::encode($binary);
    $stored = PasskeyEncoding::credentialIdForStorage($url);

    expect($stored)->toBe(base64_encode($binary));
});
