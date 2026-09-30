<?php

it('returns passkey health diagnostics as json', function () {
    $response = $this->get('/oxalis/health/passkeys');

    $response->assertOk()
        ->assertJsonStructure([
            'ok',
            'issues',
            'browser_origin',
            'browser_host',
            'rp_id',
            'origins',
            'origin_match',
            'rp_id_match',
            'passkeys_table',
            'passkey_enabled',
            'suggested_origins',
        ]);
});
