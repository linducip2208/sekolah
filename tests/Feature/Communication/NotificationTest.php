<?php

use App\Models\Communication\NotificationLog;
use App\Models\School;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->school = School::factory()->create(['settings' => []]);
    app()->instance('current_school', $this->school);

    $this->user = User::factory()->create(['school_id' => $this->school->id, 'is_active' => true]);
    $this->user->assignRole('teacher');
});

test('user can list their notifications', function () {
    Sanctum::actingAs($this->user);

    NotificationLog::create([
        'school_id' => $this->school->id,
        'user_id'   => $this->user->id,
        'type'      => 'announcement',
        'title'     => 'Test Notif',
        'is_read'   => false,
    ]);

    $response = $this->getJson('/api/v1/notifications');
    $response->assertOk();
    $data = $response->json('data');
    expect(count($data))->toBe(1);
});

test('user can get unread count', function () {
    Sanctum::actingAs($this->user);

    NotificationLog::create([
        'school_id' => $this->school->id,
        'user_id'   => $this->user->id,
        'type'      => 'fee',
        'title'     => 'Tagihan Baru',
        'is_read'   => false,
    ]);

    $response = $this->getJson('/api/v1/notifications/unread-count');
    $response->assertOk()->assertJsonPath('count', 1);
});

test('user can mark notification as read', function () {
    Sanctum::actingAs($this->user);

    $notif = NotificationLog::create([
        'school_id' => $this->school->id,
        'user_id'   => $this->user->id,
        'type'      => 'attendance',
        'title'     => 'Absensi',
        'is_read'   => false,
    ]);

    $this->postJson("/api/v1/notifications/{$notif->id}/read")->assertOk();
    $notif->refresh();
    expect($notif->is_read)->toBeTrue();
});

test('user can mark all notifications as read', function () {
    Sanctum::actingAs($this->user);

    NotificationLog::create(['school_id' => $this->school->id, 'user_id' => $this->user->id, 'type' => 'a', 'title' => 'N1', 'is_read' => false]);
    NotificationLog::create(['school_id' => $this->school->id, 'user_id' => $this->user->id, 'type' => 'b', 'title' => 'N2', 'is_read' => false]);

    $this->postJson('/api/v1/notifications/read-all')->assertOk();

    expect(NotificationLog::where('user_id', $this->user->id)->where('is_read', false)->count())->toBe(0);
});
