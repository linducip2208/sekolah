<?php

use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Medium;
use App\Models\Academic\Section;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Student;
use App\Models\Facilities\Hostel;
use App\Models\Facilities\HostelRoom;
use App\Models\School;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->school = School::factory()->create(['settings' => []]);
    app()->instance('current_school', $this->school);

    $this->admin = User::factory()->create(['school_id' => $this->school->id, 'is_active' => true]);
    $this->admin->assignRole('admin');

    $academicYear = AcademicYear::create([
        'school_id'  => $this->school->id,
        'name'       => '2024/2025',
        'start_date' => '2024-07-01',
        'end_date'   => '2025-06-30',
        'is_active'  => true,
    ]);

    $medium       = Medium::create(['school_id' => $this->school->id, 'name' => 'Indonesia']);
    $classRoom    = ClassRoom::create(['school_id' => $this->school->id, 'medium_id' => $medium->id, 'name' => 'Kelas 7']);
    $section      = Section::create(['school_id' => $this->school->id, 'name' => 'A']);
    $teacher      = User::factory()->create(['school_id' => $this->school->id]);
    $classSection = ClassSection::create([
        'school_id'        => $this->school->id,
        'class_room_id'    => $classRoom->id,
        'section_id'       => $section->id,
        'medium_id'        => $medium->id,
        'academic_year_id' => $academicYear->id,
        'class_teacher_id' => $teacher->id,
    ]);

    $studentUser   = User::factory()->create(['school_id' => $this->school->id]);
    $this->student = Student::create([
        'user_id'          => $studentUser->id,
        'school_id'        => $this->school->id,
        'class_section_id' => $classSection->id,
    ]);
});

test('admin can create hostel', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->postJson('/api/v1/hostel', [
        'name' => 'Asrama Putra',
        'type' => 'boys',
    ]);

    $response->assertStatus(201)->assertJsonPath('name', 'Asrama Putra');
    expect(Hostel::count())->toBe(1);
});

test('admin can add room to hostel', function () {
    Sanctum::actingAs($this->admin);

    $hostel = Hostel::create([
        'school_id' => $this->school->id,
        'name'      => 'Asrama Putri',
        'type'      => 'girls',
    ]);

    $response = $this->postJson("/api/v1/hostel/{$hostel->id}/rooms", [
        'room_no'  => '101',
        'capacity' => 4,
    ]);

    $response->assertStatus(201)->assertJsonPath('room_no', '101');
    expect(HostelRoom::count())->toBe(1);
});

test('admin can allocate student to room', function () {
    Sanctum::actingAs($this->admin);

    $hostel = Hostel::create([
        'school_id' => $this->school->id,
        'name'      => 'Asrama Putra',
        'type'      => 'boys',
    ]);

    $room = HostelRoom::create([
        'hostel_id' => $hostel->id,
        'room_no'   => 'A1',
        'capacity'  => 4,
        'occupied'  => 0,
    ]);

    $response = $this->postJson('/api/v1/hostel/allocate', [
        'student_id' => $this->student->id,
        'room_id'    => $room->id,
        'from_date'  => '2025-07-01',
    ]);

    $response->assertStatus(201);
    $room->refresh();
    expect($room->occupied)->toBe(1);
});
