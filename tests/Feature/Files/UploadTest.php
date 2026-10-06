<?php

use App\Events\MessageSent;
use App\Models\Communication\Conversation;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->school = School::factory()->create(['settings' => []]);
    $this->admin = User::factory()->create(['school_id' => $this->school->id, 'is_active' => true]);
    $this->admin->assignRole('admin');

    $this->teacher = User::factory()->create(['school_id' => $this->school->id, 'is_active' => true]);
    $this->teacher->assignRole('teacher');

    Storage::fake('public');
    Storage::fake('local');
});

test('image upload is stored under school-scoped public path', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->post('/api/v1/uploads', [
        'purpose' => 'chat',
        'file' => UploadedFile::fake()->image('photo.jpg', 800, 600),
    ], ['Accept' => 'application/json']);

    $response->assertStatus(201)->assertJsonPath('purpose', 'chat');
    $path = $response->json('path');
    expect($path)->toStartWith("uploads/{$this->school->id}/chat/");
    expect($response->json('url'))->toContain('/storage/');
    Storage::disk('public')->assertExists($path);
});

test('sensitive purpose is stored on private disk without public url', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->post('/api/v1/uploads', [
        'purpose' => 'ppdb',
        'file' => UploadedFile::fake()->create('doc.pdf', 200, 'application/pdf'),
    ], ['Accept' => 'application/json']);

    $response->assertStatus(201);
    expect($response->json('url'))->toBeNull();
    Storage::disk('local')->assertExists($response->json('path'));
});

test('executable upload is rejected', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->post('/api/v1/uploads', [
        'purpose' => 'chat',
        'file' => UploadedFile::fake()->create('evil.exe', 100, 'application/x-msdownload'),
    ], ['Accept' => 'application/json']);

    $response->assertStatus(422);
});

test('download rejects traversal and foreign school paths', function () {
    Sanctum::actingAs($this->admin);

    $this->getJson('/api/v1/uploads/file?path=uploads/1/chat/../../.env')->assertNotFound();
    $this->getJson('/api/v1/uploads/file?path=uploads/999999/chat/x.jpg')->assertNotFound();
    $this->getJson('/api/v1/uploads/file?path=/etc/passwd')->assertNotFound();
});

test('download serves own uploaded file', function () {
    Sanctum::actingAs($this->admin);

    $up = $this->post('/api/v1/uploads', [
        'purpose' => 'chat',
        'file' => UploadedFile::fake()->image('a.jpg', 100, 100),
    ], ['Accept' => 'application/json']);
    $up->assertStatus(201);

    $this->getJson('/api/v1/uploads/file?path='.$up->json('path'))->assertOk();
});

test('sending a message broadcasts MessageSent', function () {
    Event::fake([MessageSent::class]);
    Sanctum::actingAs($this->admin);

    $conversation = Conversation::create([
        'school_id' => $this->school->id,
        'user_one' => min($this->admin->id, $this->teacher->id),
        'user_two' => max($this->admin->id, $this->teacher->id),
    ]);

    $this->postJson("/api/v1/chat/conversations/{$conversation->id}/send", [
        'body' => 'realtime check',
    ])->assertStatus(201);

    Event::assertDispatched(MessageSent::class);
});

test('avatar upload resizes and stores school-scoped jpg', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->post('/api/v1/auth/avatar', [
        'avatar' => UploadedFile::fake()->image('me.png', 900, 900),
    ], ['Accept' => 'application/json']);

    $response->assertOk()->assertJsonPath('avatar_url', '/storage/'."avatars/{$this->school->id}/{$this->admin->id}.jpg");
});
