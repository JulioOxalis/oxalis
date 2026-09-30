<?php
namespace Oxalis\Webhooks;

use Oxalis\Models\Webhook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    public function fire(string $event, array $payload): void
    {
        if (!config('oxalis.webhooks.enabled', false)) {
            return;
        }

        try {
            $hooks = Webhook::where('active', true)->get();
        } catch (\Throwable) {
            return;
        }

        foreach ($hooks as $hook) {
            if (!$hook->listensTo($event)) {
                continue;
            }

            $body = json_encode([
                'event'   => $event,
                'fired_at'=> now()->toIso8601String(),
                'payload' => $payload,
            ]);

            try {
                Http::timeout(5)
                    ->withHeaders([
                        'Content-Type'       => 'application/json',
                        'X-Oxalis-Event'     => $event,
                        'X-Oxalis-Signature' => $hook->sign($body),
                    ])
                    ->post($hook->url, json_decode($body, true));

                $hook->update(['last_fired_at' => now(), 'failures' => 0]);
            } catch (\Throwable $e) {
                $hook->increment('failures');
                if ($hook->failures >= 10) {
                    $hook->update(['active' => false]);
                    Log::warning('Oxalis webhook disabled after 10 failures', ['url' => $hook->url]);
                }
            }
        }
    }
}
