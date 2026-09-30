<?php
namespace Oxalis\Telemetry;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TelemetryService
{
    private const CACHE_KEY = 'oxalis_telemetry_last_ping';

    public function maybePing(): void
    {
        if (!config('oxalis.telemetry', false)) {
            return;
        }

        $endpoint = config('oxalis.telemetry_endpoint');
        if (!$endpoint) {
            return;
        }

        // Fire at most once per 24 hours — use a file cache to avoid MongoDB
        if (Cache::get(self::CACHE_KEY)) {
            return;
        }

        Cache::put(self::CACHE_KEY, true, now()->addDay());

        $payload = [
            'v'        => '1.0',
            'php'      => PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION,
            'laravel'  => app()->version(),
            'methods'  => array_keys(array_filter(config('oxalis.methods', []))),
            'domain'   => hash('sha256', config('app.url', 'unknown')),
        ];

        try {
            Http::timeout(3)->asJson()->post($endpoint, $payload);
        } catch (\Throwable) {
            // Telemetry is best-effort — never crash the app
        }
    }
}
