<?php

use App\Jobs\DeliverWebhookJob;
use App\Models\Communication\Webhook;
use App\Models\Communication\WebhookDelivery;
use App\Models\School;
use App\Services\Webhook\WebhookDispatcher;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

test('webhook dispatcher fires for matching events', function () {
    Bus::fake();
    $school = School::factory()->create();

    Webhook::create([
        'school_id'   => $school->id,
        'name'        => 'Slack',
        'url'         => 'https://example.com/hook',
        'events'      => ['student.created', 'invoice.paid'],
        'is_active'   => true,
        'max_retries' => 3,
    ]);

    $count = app(WebhookDispatcher::class)->fire($school->id, 'student.created', ['id' => 1, 'name' => 'X']);

    expect($count)->toBe(1);
    Bus::assertDispatched(DeliverWebhookJob::class);
    expect(WebhookDelivery::count())->toBe(1);
});

test('webhook dispatcher skips non-matching events', function () {
    Bus::fake();
    $school = School::factory()->create();

    Webhook::create([
        'school_id'   => $school->id,
        'name'        => 'Only Paid',
        'url'         => 'https://example.com/hook',
        'events'      => ['invoice.paid'],
        'is_active'   => true,
        'max_retries' => 3,
    ]);

    $count = app(WebhookDispatcher::class)->fire($school->id, 'student.created', ['id' => 1]);
    expect($count)->toBe(0);
    Bus::assertNotDispatched(DeliverWebhookJob::class);
});

test('webhook delivery job marks success on 2xx', function () {
    $school = School::factory()->create();
    $webhook = Webhook::create([
        'school_id'   => $school->id,
        'name'        => 'OK',
        'url'         => 'https://api.example.com/in',
        'events'      => ['ping'],
        'is_active'   => true,
        'max_retries' => 3,
    ]);

    $delivery = WebhookDelivery::create([
        'webhook_id' => $webhook->id,
        'school_id'  => $school->id,
        'event'      => 'ping',
        'payload'    => '{"ok":true}',
        'status'     => 'pending',
        'attempts'   => 0,
    ]);

    Http::fake(['api.example.com/*' => Http::response('ok', 200)]);

    (new DeliverWebhookJob($delivery->id))->handle();

    $delivery->refresh();
    expect($delivery->status)->toBe('success');
    expect($delivery->http_status)->toBe(200);
});

test('webhook delivery schedules retry on failure', function () {
    Bus::fake();
    $school = School::factory()->create();
    $webhook = Webhook::create([
        'school_id'   => $school->id,
        'name'        => 'Fail',
        'url'         => 'https://api.example.com/in',
        'events'      => ['ping'],
        'is_active'   => true,
        'max_retries' => 3,
    ]);

    $delivery = WebhookDelivery::create([
        'webhook_id' => $webhook->id,
        'school_id'  => $school->id,
        'event'      => 'ping',
        'payload'    => '{"ok":true}',
        'status'     => 'pending',
        'attempts'   => 0,
    ]);

    Http::fake(['api.example.com/*' => Http::response('boom', 500)]);

    (new DeliverWebhookJob($delivery->id))->handle();

    $delivery->refresh();
    expect($delivery->status)->toBe('retrying');
    expect($delivery->attempts)->toBe(1);
    Bus::assertDispatched(DeliverWebhookJob::class);
});
