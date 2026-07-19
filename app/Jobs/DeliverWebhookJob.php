<?php

namespace App\Jobs;

use App\Models\Communication\WebhookDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DeliverWebhookJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(public int $deliveryId) {}

    public function handle(): void
    {
        $delivery = WebhookDelivery::with('webhook')->find($this->deliveryId);
        if (!$delivery || !$delivery->webhook || !$delivery->webhook->is_active) {
            return;
        }
        $webhook = $delivery->webhook;

        $payload = $delivery->payload;
        $secret  = $webhook->secret;
        $sig     = $secret ? hash_hmac('sha256', $payload, $secret) : null;

        $headers = array_merge([
            'Content-Type'      => 'application/json',
            'X-Webhook-Event'   => $delivery->event,
            'X-Webhook-Id'      => $delivery->event_id ?? '',
            'X-Webhook-Attempt' => (string) ($delivery->attempts + 1),
        ], $webhook->extra_headers ?? []);

        if ($sig) {
            $headers['X-Webhook-Signature'] = 'sha256=' . $sig;
        }

        $delivery->attempts++;

        try {
            $resp = Http::withHeaders($headers)->timeout(15)->withBody($payload, 'application/json')->post($webhook->url);
            $delivery->http_status   = $resp->status();
            $delivery->response_body = \Illuminate\Support\Str::limit($resp->body(), 4000);

            if ($resp->successful()) {
                $delivery->status        = 'success';
                $delivery->delivered_at  = now();
                $webhook->last_delivered_at = now();
                $webhook->save();
            } else {
                $this->scheduleRetry($delivery);
            }
        } catch (\Throwable $e) {
            $delivery->response_body = $e->getMessage();
            $this->scheduleRetry($delivery);
        }

        $delivery->save();
    }

    private function scheduleRetry(WebhookDelivery $delivery): void
    {
        $maxRetries = $delivery->webhook->max_retries ?? 3;
        if ($delivery->attempts >= $maxRetries) {
            $delivery->status = 'failed';
            $delivery->webhook->last_failed_at = now();
            $delivery->webhook->save();
            return;
        }
        $delivery->status = 'retrying';
        $backoffSeconds   = 60 * (2 ** ($delivery->attempts - 1)); // 60, 120, 240
        $delivery->next_retry_at = now()->addSeconds($backoffSeconds);
        $delivery->save();

        self::dispatch($delivery->id)->delay(now()->addSeconds($backoffSeconds));
    }
}
