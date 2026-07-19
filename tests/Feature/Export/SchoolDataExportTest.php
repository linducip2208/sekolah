<?php

use App\Jobs\ExportSchoolDataJob;
use App\Models\School;
use App\Models\SchoolDataExport;
use App\Models\User;
use Illuminate\Support\Facades\Bus;

test('admin can request data export', function () {
    $school = School::factory()->create();
    $user = User::factory()->create(['school_id' => $school->id, 'is_active' => true]);
    $user->assignRole('admin');

    Bus::fake();
    $this->actingAs($user)
        ->post('/admin/exports')
        ->assertRedirect();

    Bus::assertDispatched(ExportSchoolDataJob::class);
    expect(SchoolDataExport::where('school_id', $school->id)->count())->toBe(1);
});

test('admin cannot request export when one already running', function () {
    $school = School::factory()->create();
    $user = User::factory()->create(['school_id' => $school->id, 'is_active' => true]);
    $user->assignRole('admin');

    SchoolDataExport::create([
        'school_id'    => $school->id,
        'requested_by' => $user->id,
        'format'       => 'zip',
        'status'       => 'processing',
    ]);

    Bus::fake();
    $this->actingAs($user)
        ->post('/admin/exports')
        ->assertSessionHasErrors('export');

    Bus::assertNotDispatched(ExportSchoolDataJob::class);
});

test('admin cannot download other school export', function () {
    $a = School::factory()->create();
    $b = School::factory()->create();
    $userA = User::factory()->create(['school_id' => $a->id, 'is_active' => true]);
    $userA->assignRole('admin');

    $export = SchoolDataExport::create([
        'school_id'    => $b->id,
        'requested_by' => $userA->id,
        'format'       => 'zip',
        'status'       => 'completed',
        'file_path'    => 'exports/fake.zip',
        'expires_at'   => now()->addDay(),
    ]);

    $this->actingAs($userA)
        ->get(route('admin.exports.download', $export))
        ->assertForbidden();
});
