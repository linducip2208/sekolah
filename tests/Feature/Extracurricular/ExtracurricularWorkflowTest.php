<?php

use App\Models\Academic\Student;
use App\Models\Extracurricular\Extracurricular;
use App\Models\Plan;
use App\Models\School;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->school = School::factory()->create(['plan_id' => Plan::factory()->create()->id]);
    $this->admin = User::factory()->create(['school_id' => $this->school->id]);
    $this->admin->assignRole('admin');
    $this->student = Student::factory()->create(['school_id' => $this->school->id]);
    Sanctum::actingAs($this->admin);
});

it('rejects enrollment for a student from another school', function () {
    $foreignSchool = School::factory()->create(['plan_id' => Plan::factory()->create()->id]);
    $foreignStudent = Student::factory()->create(['school_id' => $foreignSchool->id]);
    $ekskul = Extracurricular::create([
        'school_id' => $this->school->id,
        'name' => 'Robotik',
        'capacity' => 5,
        'is_active' => true,
    ]);

    $this->postJson("/api/v1/ekskul/{$ekskul->id}/enroll", [
        'student_id' => $foreignStudent->id,
    ])->assertNotFound();
});

it('enforces extracurricular capacity and keeps enrollment idempotent', function () {
    $secondStudent = Student::factory()->create(['school_id' => $this->school->id]);
    $ekskul = Extracurricular::create([
        'school_id' => $this->school->id,
        'name' => 'Robotik',
        'capacity' => 1,
        'is_active' => true,
    ]);

    $this->postJson("/api/v1/ekskul/{$ekskul->id}/enroll", ['student_id' => $this->student->id])
        ->assertOk();
    $this->postJson("/api/v1/ekskul/{$ekskul->id}/enroll", ['student_id' => $this->student->id])
        ->assertOk();
    $this->postJson("/api/v1/ekskul/{$ekskul->id}/enroll", ['student_id' => $secondStudent->id])
        ->assertStatus(422);

    expect(DB::table('student_extracurriculars')
        ->where('extracurricular_id', $ekskul->id)
        ->where('student_id', $this->student->id)
        ->count())->toBe(1);
});

it('only records attendance for active extracurricular members', function () {
    $ekskul = Extracurricular::create([
        'school_id' => $this->school->id,
        'name' => 'Robotik',
        'capacity' => 5,
        'is_active' => true,
    ]);

    $this->postJson("/api/v1/ekskul/{$ekskul->id}/attendance", [
        'session_date' => today()->toDateString(),
        'attendances' => [['student_id' => $this->student->id, 'status' => 'present']],
    ])->assertStatus(422);

    $this->postJson("/api/v1/ekskul/{$ekskul->id}/enroll", ['student_id' => $this->student->id])
        ->assertOk();
    $this->postJson("/api/v1/ekskul/{$ekskul->id}/attendance", [
        'session_date' => today()->toDateString(),
        'attendances' => [['student_id' => $this->student->id, 'status' => 'present']],
    ])->assertOk();
});
