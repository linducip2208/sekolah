<?php

use App\Models\Communication\Notice;
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

test('admin can create a notice', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->postJson('/api/v1/notices', [
        'title'        => 'Pengumuman Libur',
        'content'      => 'Sekolah akan libur besok.',
        'is_published' => true,
        'target_roles' => ['student', 'parent'],
    ]);

    $response->assertStatus(201)->assertJsonPath('title', 'Pengumuman Libur');
    expect(Notice::count())->toBe(1);
});

test('published notices appear in user feed', function () {
    Sanctum::actingAs($this->teacher);

    Notice::create([
        'school_id'    => $this->school->id,
        'created_by'   => $this->admin->id,
        'title'        => 'Untuk Guru',
        'content'      => 'Rapat besok.',
        'target_roles' => ['teacher'],
        'is_published' => true,
        'publish_at'   => now()->subMinute(),
    ]);

    $response = $this->getJson('/api/v1/notices');
    $response->assertOk()->assertJsonCount(1);
});

test('admin can delete a notice', function () {
    Sanctum::actingAs($this->admin);

    $notice = Notice::create([
        'school_id'  => $this->school->id,
        'created_by' => $this->admin->id,
        'title'      => 'To Delete',
        'content'    => 'Content',
    ]);

    $this->deleteJson("/api/v1/notices/{$notice->id}")->assertOk();
    expect(Notice::withTrashed()->find($notice->id)->deleted_at)->not->toBeNull();
});
