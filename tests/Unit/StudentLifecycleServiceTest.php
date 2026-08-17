<?php

use App\Models\Academic\Student;
use App\Models\Academic\StudentStatusHistory;
use App\Models\School;
use App\Models\User;
use App\Services\Academic\StudentLifecycleService;

beforeEach(function () {
    $this->service = app(StudentLifecycleService::class);
    $this->school = School::factory()->create();
    $user = User::factory()->create(['school_id' => $this->school->id]);
    $this->student = Student::create([
        'user_id' => $user->id, 'school_id' => $this->school->id, 'admission_no' => 'LC-1', 'status' => 'active',
    ]);
});

it('transitions status with validation and records history', function () {
    $graduated = $this->service->transition($this->student, 'graduated', 'Lulus 2026');

    expect($graduated->status)->toBe('graduated');
    expect($graduated->graduated_at)->not->toBeNull();

    expect(StudentStatusHistory::where('student_id', $this->student->id)->count())->toBe(1);
    $this->assertDatabaseHas('student_status_history', [
        'student_id' => $this->student->id, 'from_status' => 'active', 'to_status' => 'graduated',
    ]);
});

it('walks the full lifecycle active -> graduated -> alumni', function () {
    $this->service->transition($this->student, 'graduated');
    $alumni = $this->service->transition($this->student->fresh(), 'alumni');

    expect($alumni->status)->toBe('alumni');
});

it('rejects an invalid transition', function () {
    $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    $this->service->transition($this->student, 'alumni'); // active -> alumni not allowed directly
});

it('records enrollment when transitioning from applicant to enrolled', function () {
    $applicant = Student::create([
        'user_id' => $this->student->user_id, 'school_id' => $this->school->id,
        'admission_no' => 'LC-2', 'status' => 'applicant',
    ]);

    $enrolled = $this->service->transition($applicant, 'enrolled');

    expect($enrolled->status)->toBe('enrolled');
    expect($enrolled->enrolled_at)->not->toBeNull();
});
