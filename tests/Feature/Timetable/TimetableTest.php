<?php

use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Medium;
use App\Models\Academic\Section;
use App\Models\Academic\Subject;
use App\Models\Academic\TimetableSlot;
use App\Models\School;
use App\Models\User;
use App\Services\Academic\TimetableService;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->school = School::factory()->create(['settings' => []]);
    app()->instance('current_school', $this->school);

    $this->admin = User::factory()->create([
        'school_id' => $this->school->id,
        'is_active' => true,
    ]);
    $this->admin->assignRole('admin');

    $this->teacher = User::factory()->create([
        'school_id' => $this->school->id,
        'is_active' => true,
    ]);
    $this->teacher->assignRole('teacher');

    $academicYear = AcademicYear::create([
        'school_id'  => $this->school->id,
        'name'       => '2024/2025',
        'start_date' => '2024-07-01',
        'end_date'   => '2025-06-30',
        'is_active'  => true,
    ]);

    $medium    = Medium::create(['school_id' => $this->school->id, 'name' => 'Bahasa Indonesia']);
    $classRoom = ClassRoom::create(['school_id' => $this->school->id, 'medium_id' => $medium->id, 'name' => 'Kelas 10']);
    $section   = Section::create(['school_id' => $this->school->id, 'name' => 'A']);

    $this->classSection = ClassSection::create([
        'school_id'        => $this->school->id,
        'class_room_id'    => $classRoom->id,
        'section_id'       => $section->id,
        'medium_id'        => $medium->id,
        'academic_year_id' => $academicYear->id,
        'class_teacher_id' => $this->teacher->id,
    ]);

    $this->subject = Subject::create([
        'school_id' => $this->school->id,
        'medium_id' => $medium->id,
        'name'      => 'Matematika',
        'code'      => 'MTK',
        'type'      => 'theory',
    ]);
});

test('admin can create a timetable slot', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->postJson('/api/v1/timetable', [
        'class_section_id' => $this->classSection->id,
        'subject_id'       => $this->subject->id,
        'teacher_id'       => $this->teacher->id,
        'day_of_week'      => 1,
        'start_time'       => '07:00',
        'end_time'         => '08:30',
        'room'             => 'Ruang 10A',
    ]);

    $response->assertStatus(201);
    expect(TimetableSlot::count())->toBe(1);
});

test('teacher conflict is detected', function () {
    Sanctum::actingAs($this->admin);

    TimetableSlot::create([
        'school_id'        => $this->school->id,
        'class_section_id' => $this->classSection->id,
        'subject_id'       => $this->subject->id,
        'teacher_id'       => $this->teacher->id,
        'day_of_week'      => 1,
        'start_time'       => '07:00',
        'end_time'         => '08:30',
    ]);

    $service   = app(TimetableService::class);
    $conflicts = $service->checkConflict([
        'class_section_id' => $this->classSection->id,
        'subject_id'       => $this->subject->id,
        'teacher_id'       => $this->teacher->id,
        'day_of_week'      => 1,
        'start_time'       => '08:00',
        'end_time'         => '09:30',
    ]);

    expect($conflicts)->not->toBeEmpty()
        ->and($conflicts[0]['type'])->toBe('teacher_conflict');
});

test('class conflict is detected', function () {
    Sanctum::actingAs($this->admin);

    $teacher2  = User::factory()->create(['school_id' => $this->school->id]);
    $medium    = Medium::where('school_id', $this->school->id)->first();
    $subject2  = Subject::create([
        'school_id' => $this->school->id,
        'medium_id' => $medium->id,
        'name'      => 'Fisika',
        'code'      => 'FIS',
        'type'      => 'theory',
    ]);

    TimetableSlot::create([
        'school_id'        => $this->school->id,
        'class_section_id' => $this->classSection->id,
        'subject_id'       => $this->subject->id,
        'teacher_id'       => $this->teacher->id,
        'day_of_week'      => 2,
        'start_time'       => '07:00',
        'end_time'         => '08:30',
    ]);

    $service   = app(TimetableService::class);
    $conflicts = $service->checkConflict([
        'class_section_id' => $this->classSection->id,
        'subject_id'       => $subject2->id,
        'teacher_id'       => $teacher2->id,
        'day_of_week'      => 2,
        'start_time'       => '07:30',
        'end_time'         => '09:00',
    ]);

    $types = collect($conflicts)->pluck('type')->toArray();
    expect($types)->toContain('class_conflict');
});

test('non-overlapping slots have no conflict', function () {
    Sanctum::actingAs($this->admin);

    TimetableSlot::create([
        'school_id'        => $this->school->id,
        'class_section_id' => $this->classSection->id,
        'subject_id'       => $this->subject->id,
        'teacher_id'       => $this->teacher->id,
        'day_of_week'      => 1,
        'start_time'       => '07:00',
        'end_time'         => '08:30',
    ]);

    $service   = app(TimetableService::class);
    $conflicts = $service->checkConflict([
        'class_section_id' => $this->classSection->id,
        'subject_id'       => $this->subject->id,
        'teacher_id'       => $this->teacher->id,
        'day_of_week'      => 1,
        'start_time'       => '09:00',
        'end_time'         => '10:30',
    ]);

    expect($conflicts)->toBeEmpty();
});

test('weekly schedule is grouped by day', function () {
    Sanctum::actingAs($this->admin);

    TimetableSlot::create([
        'school_id'        => $this->school->id,
        'class_section_id' => $this->classSection->id,
        'subject_id'       => $this->subject->id,
        'teacher_id'       => $this->teacher->id,
        'day_of_week'      => 1,
        'start_time'       => '07:00',
        'end_time'         => '08:30',
    ]);

    TimetableSlot::create([
        'school_id'        => $this->school->id,
        'class_section_id' => $this->classSection->id,
        'subject_id'       => $this->subject->id,
        'teacher_id'       => $this->teacher->id,
        'day_of_week'      => 2,
        'start_time'       => '07:00',
        'end_time'         => '08:30',
    ]);

    $response = $this->getJson("/api/v1/timetable/class/{$this->classSection->id}");

    $response->assertOk()->assertJsonStructure(['slots', 'breaks']);
    expect($response->json('slots'))->toHaveKeys(['1', '2']);
});

test('admin can delete a timetable slot', function () {
    Sanctum::actingAs($this->admin);

    $slot = TimetableSlot::create([
        'school_id'        => $this->school->id,
        'class_section_id' => $this->classSection->id,
        'subject_id'       => $this->subject->id,
        'teacher_id'       => $this->teacher->id,
        'day_of_week'      => 3,
        'start_time'       => '07:00',
        'end_time'         => '08:30',
    ]);

    $this->deleteJson("/api/v1/timetable/{$slot->id}")->assertOk();
    expect(TimetableSlot::withTrashed()->find($slot->id)->deleted_at)->not->toBeNull();
});
