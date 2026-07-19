<?php

use App\Models\Communication\NotificationProvider;
use App\Models\School;
use App\Models\User;
use App\Services\Notification\NotificationDispatcher;
use Illuminate\Support\Facades\Http;

test('encrypted credentials are stored and decrypted', function () {
    $school = School::factory()->create();
    $provider = new NotificationProvider();
    $provider->school_id = $school->id;
    $provider->name = 'Push Test';
    $provider->transport = 'push';
    $provider->api_format = 'fcm_legacy';
    $provider->api_key = 'super-secret-key';
    $provider->save();

    expect($provider->api_key_encrypted)->not->toBe('super-secret-key');
    expect($provider->fresh()->api_key)->toBe('super-secret-key');
});

test('dispatcher picks default provider per transport', function () {
    $school = School::factory()->create();

    NotificationProvider::create([
        'school_id'  => $school->id,
        'name'       => 'A',
        'transport'  => 'sms',
        'api_format' => 'rest_generic',
        'is_active'  => true,
        'is_default' => false,
        'base_url'   => 'https://a.example.com/send',
    ]);
    $b = NotificationProvider::create([
        'school_id'  => $school->id,
        'name'       => 'B',
        'transport'  => 'sms',
        'api_format' => 'rest_generic',
        'is_active'  => true,
        'is_default' => true,
        'base_url'   => 'https://b.example.com/send',
    ]);

    $picked = app(NotificationDispatcher::class)->getProvider($school->id, 'sms');
    expect($picked->id)->toBe($b->id);
});

test('rest generic adapter sends POST with bearer auth', function () {
    Http::fake(['gateway.example.com/*' => Http::response('ok', 200)]);

    $school = School::factory()->create();
    $provider = NotificationProvider::create([
        'school_id'    => $school->id,
        'name'         => 'Gen',
        'transport'    => 'sms',
        'api_format'   => 'rest_generic',
        'is_active'    => true,
        'base_url'     => 'https://gateway.example.com/send',
        'extra_config' => ['method' => 'POST', 'auth' => 'bearer', 'to_field' => 'to', 'message_field' => 'text'],
    ]);
    $provider->api_key = 'tk-123';
    $provider->save();

    $adapter = new \App\Services\Notification\Adapters\RestGenericAdapter();
    $result = $adapter->send($provider->fresh(), ['+628111'], 'T', 'B');

    expect($result['sent'])->toBe(1);
    Http::assertSent(fn($req) => $req->hasHeader('Authorization', 'Bearer tk-123')
        && $req['to'] === '+628111'
        && $req['text'] === 'B');
});
