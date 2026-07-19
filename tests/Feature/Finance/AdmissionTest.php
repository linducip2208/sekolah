<?php

use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Medium;
use App\Models\Academic\Section;
use App\Models\Finance\AdmissionEnquiry;
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

    $medium    = Medium::create(['school_id' => $this->school->id, 'name' => 'Indonesia']);
    $classRoom = ClassRoom::create(['school_id' => $this->school->id, 'medium_id' => $medium->id, 'name' => 'Kelas 7']);
    $section   = Section::create(['school_id' => $this->school->id, 'name' => 'A']);

    $teacher = User::factory()->create(['school_id' => $this->school->id]);
    $this->classSection = ClassSection::create([
        'school_id'        => $this->school->id,
        'class_room_id'    => $classRoom->id,
        'section_id'       => $section->id,
        'medium_id'        => $medium->id,
        'academic_year_id' => $academicYear->id,
        'class_teacher_id' => $teacher->id,
    ]);
});

test('admin can create admission enquiry', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->postJson('/api/v1/admission', [
        'student_name' => 'Budi Santoso',
        'father_name'  => 'Ayah Budi',
        'phone'        => '08123456789',
        'class_applying' => 'Kelas 7',
    ]);

    $response->assertStatus(201)->assertJsonPath('student_name', 'Budi Santoso');
    expect(AdmissionEnquiry::count())->toBe(1);
});

test('admin can list admission enquiries', function () {
    Sanctum::actingAs($this->admin);

    AdmissionEnquiry::create([
        'school_id'    => $this->school->id,
        'student_name' => 'Test Student',
        'phone'        => '08111222333',
        'status'       => 'enquiry',
    ]);

    $response = $this->getJson('/api/v1/admission');
    $response->assertOk()->assertJsonCount(1);
});

test('admin can enroll student from enquiry', function () {
    Sanctum::actingAs($this->admin);

    $enquiry = AdmissionEnquiry::create([
        'school_id'    => $this->school->id,
        'student_name' => 'Siswa Baru',
        'phone'        => '08123456789',
        'status'       => 'applied',
    ]);

    $response = $this->postJson("/api/v1/admission/{$enquiry->id}/enroll", [
        'class_section_id' => $this->classSection->id,
    ]);

    $response->assertStatus(201);
    $enquiry->refresh();
    expect($enquiry->status)->toBe('enrolled')
        ->and($enquiry->converted_student_id)->not->toBeNull();
});
