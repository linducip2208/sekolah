<?php

use App\Models\Academic\AcademicYear;
use App\Models\Academic\ReportCard;
use App\Models\Academic\Semester;
use App\Models\Academic\Student;
use App\Models\School;
use App\Models\User;
use App\Services\Academic\GradeApprovalService;

beforeEach(function () {
    $this->service = app(GradeApprovalService::class);
    $this->school = School::factory()->create();
    $user = User::factory()->create(['school_id' => $this->school->id]);
    $this->user = $user;
    $student = Student::create([
        'user_id' => $user->id, 'school_id' => $this->school->id, 'admission_no' => 'G-1',
    ]);
    $year = AcademicYear::create([
        'school_id' => $this->school->id, 'name' => '2025/2026',
        'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true,
    ]);
    $semester = Semester::create([
        'school_id' => $this->school->id, 'academic_year_id' => $year->id,
        'name' => 'Ganjil', 'start_date' => '2025-07-01', 'end_date' => '2025-12-31', 'is_active' => true,
    ]);
    $this->card = ReportCard::create([
        'school_id' => $this->school->id, 'student_id' => $student->id, 'semester_id' => $semester->id,
        'total_percentage' => 88, 'overall_grade' => 'A', 'status' => 'draft', 'is_published' => false,
    ]);
});

it('walks the approval flow draft -> submitted -> approved -> locked', function () {
    $submitted = $this->service->submit($this->card);
    expect($submitted->status)->toBe('submitted');

    $approved = $this->service->approve($submitted, $this->user->id);
    expect($approved->status)->toBe('approved');
    expect($approved->is_published)->toBeTrue();
    expect($approved->approved_by)->toBe($this->user->id);

    $locked = $this->service->lock($approved);
    expect($locked->status)->toBe('locked');
    expect($locked->locked_at)->not->toBeNull();
});

it('rejects invalid transitions', function () {
    $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    $this->service->approve($this->card, $this->user->id);
});

it('rejects a submitted card back to draft', function () {
    $submitted = $this->service->submit($this->card);
    $rejected  = $this->service->reject($submitted);

    expect($rejected->status)->toBe('draft');
});
