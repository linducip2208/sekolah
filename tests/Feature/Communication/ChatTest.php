<?php

use App\Models\Communication\Conversation;
use App\Models\School;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->school = School::factory()->create(['settings' => []]);
    app()->instance('current_school', $this->school);

    $this->admin = User::factory()->create(['school_id' => $this->school->id, 'is_active' => true]);
    $this->admin->assignRole('admin');

    $this->teacher = User::factory()->create(['school_id' => $this->school->id, 'is_active' => true]);
    $this->teacher->assignRole('teacher');
});

test('user can start a conversation', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->postJson('/api/v1/chat/conversations', [
        'recipient_id' => $this->teacher->id,
    ]);

    $response->assertOk();
    expect(Conversation::count())->toBe(1);
});

test('duplicate conversation is not created', function () {
    Sanctum::actingAs($this->admin);

    $this->postJson('/api/v1/chat/conversations', ['recipient_id' => $this->teacher->id]);
    $this->postJson('/api/v1/chat/conversations', ['recipient_id' => $this->teacher->id]);

    expect(Conversation::count())->toBe(1);
});

test('user can send message in conversation', function () {
    Sanctum::actingAs($this->admin);

    $userOne = min($this->admin->id, $this->teacher->id);
    $userTwo = max($this->admin->id, $this->teacher->id);

    $conversation = Conversation::create([
        'school_id'  => $this->school->id,
        'user_one'   => $userOne,
        'user_two'   => $userTwo,
    ]);

    $response = $this->postJson("/api/v1/chat/conversations/{$conversation->id}/send", [
        'body' => 'Halo, ada perlu?',
    ]);

    $response->assertStatus(201)->assertJsonPath('body', 'Halo, ada perlu?');
    expect($conversation->messages()->count())->toBe(1);
});

test('user can list conversations', function () {
    Sanctum::actingAs($this->admin);

    $userOne = min($this->admin->id, $this->teacher->id);
    $userTwo = max($this->admin->id, $this->teacher->id);

    Conversation::create([
        'school_id' => $this->school->id,
        'user_one'  => $userOne,
        'user_two'  => $userTwo,
    ]);

    $response = $this->getJson('/api/v1/chat/conversations');
    $response->assertOk()->assertJsonCount(1);
});
