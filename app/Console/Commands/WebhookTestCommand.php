<?php

namespace App\Console\Commands;

use App\Jobs\DeliverWebhookJob;
use App\Models\Communication\Webhook;
use App\Models\Communication\WebhookDelivery;
use App\Services\Webhook\WebhookDispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class WebhookTestCommand extends Command
{
    protected $signature = 'webhook:test
        {webhook : Webhook ID}
        {--event=test.ping : Event name to fire}
        {--payload= : JSON payload string (default: simple sample)}
        {--sync : Run delivery inline instead of queuing}';

    protected $description = 'Send a test payload through a configured webhook and report signature + roundtrip.';

    public function handle(WebhookDispatcher $dispatcher): int
    {
        $webhook = Webhook::find((int) $this->argument('webhook'));
        if (!$webhook) {
            $this->error('Webhook not found.');
            return self::FAILURE;
        }

        $this->info("Target: {$webhook->name} ({$webhook->url})");
        $this->line("Events: " . implode(', ', $webhook->events ?? []));

        $payload = $this->option('payload');
        $data = $payload ? json_decode($payload, true) : ['ping' => true, 'at' => now()->toIso8601String()];
        if ($payload && !is_array($data)) {
            $this->error('Invalid JSON in --payload.');
            return self::FAILURE;
        }

        $event = $this->option('event');
        $eventId = (string) Str::uuid();

        $body = json_encode([
            'event'      => $event,
            'event_id'   => $eventId,
            'occurred_at'=> now()->toIso8601String(),
            'school_id'  => $webhook->school_id,
            'data'       => $data,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $secret = $webhook->secret;
        if ($secret) {
            $sig = hash_hmac('sha256', $body, $secret);
            $this->line("Signature header: <fg=cyan>X-Webhook-Signature: sha256={$sig}</>");
            $this->line("Verify recipient-side with: <fg=cyan>hash_hmac('sha256', raw_body, your_secret)</>");
        } else {
            $this->warn('No secret configured — payload will be sent unsigned.');
        }

        $delivery = WebhookDelivery::create([
            'webhook_id' => $webhook->id,
            'school_id'  => $webhook->school_id,
            'event'      => $event,
            'event_id'   => $eventId,
            'payload'    => $body,
            'status'     => 'pending',
            'attempts'   => 0,
        ]);
        $this->line("Delivery ID: <fg=cyan>#{$delivery->id}</>");

        if ($this->option('sync')) {
            (new DeliverWebhookJob($delivery->id))->handle();
            $delivery->refresh();
            $this->line("Status: <fg=yellow>{$delivery->status}</>  HTTP: " . ($delivery->http_status ?? '—'));
            if ($delivery->response_body) {
                $this->line("Response: " . Str::limit($delivery->response_body, 400));
            }
        } else {
            DeliverWebhookJob::dispatch($delivery->id);
            $this->info('Dispatched to queue. Run `php artisan queue:work` to process.');
        }

        return self::SUCCESS;
    }
}
