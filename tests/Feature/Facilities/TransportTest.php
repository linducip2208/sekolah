<?php

use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Medium;
use App\Models\Academic\Section;
use App\Models\Academic\Student;
use App\Models\Facilities\TransportRoute;
use App\Models\Facilities\Vehicle;
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

test('admin can create transport route', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->postJson('/api/v1/transport/routes', [
        'name'          => 'Rute Selatan',
        'fee_per_month' => 15000000,
    ]);

    $response->assertStatus(201)->assertJsonPath('name', 'Rute Selatan');
    expect(TransportRoute::count())->toBe(1);
});

test('admin can register a vehicle', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->postJson('/api/v1/transport/vehicles', [
        'registration_no' => 'B 1234 XYZ',
        'make_model'      => 'Hino',
        'capacity'        => 40,
    ]);

    $response->assertStatus(201)->assertJsonPath('registration_no', 'B 1234 XYZ');
    expect(Vehicle::count())->toBe(1);
});

test('admin can assign student to transport route', function () {
    Sanctum::actingAs($this->admin);

    $route = TransportRoute::create([
        'school_id'     => $this->school->id,
        'name'          => 'Rute Utara',
        'fee_per_month' => 10000000,
        'is_active'     => true,
    ]);

    $response = $this->postJson('/api/v1/transport/assign-student', [
        'student_id'         => $this->student->id,
        'transport_route_id' => $route->id,
    ]);

    $response->assertStatus(201);
    expect(\App\Models\Facilities\StudentTransport::where('student_id', $this->student->id)->where('is_active', true)->count())->toBe(1);
});
