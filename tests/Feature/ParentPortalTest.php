<?php

use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Medium;
use App\Models\Academic\Section;
use App\Models\Academic\Student;
use App\Models\School;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->school = School::factory()->create(['settings' => []]);
    app()->instance('current_school', $this->school);

    $this->parent = User::factory()->create(['school_id' => $this->school->id, 'is_active' => true]);
    $this->parent->assignRole('parent');

    $academicYear = AcademicYear::create([
        'school_id'  => $this->school->id, 'name' => '2024/2025',
        'start_date' => '2024-07-01', 'end_date' => '2025-06-30', 'is_active' => true,
    ]);
    $medium       = Medium::create(['school_id' => $this->school->id, 'name' => 'Indonesia']);
    $classRoom    = ClassRoom::create(['school_id' => $this->school->id, 'medium_id' => $medium->id, 'name' => 'Kelas 7']);
    $section      = Section::create(['school_id' => $this->school->id, 'name' => 'A']);
    $teacher      = User::factory()->create(['school_id' => $this->school->id]);
    $classSection = ClassSection::create([
        'school_id' => $this->school->id, 'class_room_id' => $classRoom->id,
        'section_id' => $section->id, 'medium_id' => $medium->id,
        'academic_year_id' => $academicYear->id, 'class_teacher_id' => $teacher->id,
    ]);

    $studentUser   = User::factory()->create(['school_id' => $this->school->id]);
    $this->student = Student::create([
        'user_id' => $studentUser->id, 'school_id' => $this->school->id,
        'class_section_id' => $classSection->id,
    ]);

    // Link parent to student
    $this->student->parents()->attach($this->parent->id);
});

test('parent can view their children', function () {
    Sanctum::actingAs($this->parent);

    $response = $this->getJson('/api/v1/parent/children');
    $response->assertOk()->assertJsonCount(1);
});

test('parent can view child attendance', function () {
    Sanctum::actingAs($this->parent);

    $response = $this->getJson("/api/v1/parent/children/{$this->student->id}/attendance");
    $response->assertOk();
});

test('parent cannot view another student data', function () {
    Sanctum::actingAs($this->parent);

    $otherUser    = User::factory()->create(['school_id' => $this->school->id]);
    $otherStudent = Student::create([
        'user_id'          => $otherUser->id,
        'school_id'        => $this->school->id,
        'class_section_id' => $this->student->class_section_id,
    ]);

    $this->getJson("/api/v1/parent/children/{$otherStudent->id}/attendance")->assertStatus(403);
});
