<?php

namespace App\Services\Webhook;

use App\Jobs\DeliverWebhookJob;
use App\Models\Communication\Webhook;
use App\Models\Communication\WebhookDelivery;
use Illuminate\Support\Str;

class WebhookDispatcher
{
    public function fire(int $schoolId, string $event, array $payload, ?string $eventId = null): int
    {
        $webhooks = Webhook::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->get()
            ->filter(fn($w) => in_array($event, $w->events ?? [], true));

        $eventId ??= (string) Str::uuid();
        $body = json_encode([
            'event'      => $event,
            'event_id'   => $eventId,
            'occurred_at'=> now()->toIso8601String(),
            'school_id'  => $schoolId,
            'data'       => $payload,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $dispatched = 0;
        foreach ($webhooks as $webhook) {
            $delivery = WebhookDelivery::create([
                'webhook_id' => $webhook->id,
                'school_id'  => $schoolId,
                'event'      => $event,
                'event_id'   => $eventId,
                'payload'    => $body,
                'status'     => 'pending',
                'attempts'   => 0,
            ]);
            DeliverWebhookJob::dispatch($delivery->id);
            $dispatched++;
        }
        return $dispatched;
    }
}
