<?php

use App\Models\Academic\Student;
use App\Models\AI\AiRecommendation;
use App\Models\Analytics\StudentRiskScore;
use App\Models\School;
use App\Models\User;
use App\Services\AI\RecommendationService;

beforeEach(function () {
    $this->service = app(RecommendationService::class);
    $this->school = School::factory()->create();
    $user = User::factory()->create(['school_id' => $this->school->id]);
    $this->student = Student::create([
        'user_id' => $user->id, 'school_id' => $this->school->id, 'admission_no' => 'R-1', 'status' => 'active',
    ]);
});

it('generates recommendations for at-risk students with dedupe', function () {
    StudentRiskScore::create([
        'school_id' => $this->school->id, 'student_id' => $this->student->id,
        'snapshot_date' => now()->toDateString(), 'risk_level' => 'high',
        'attendance_score' => 20, 'academic_score' => 40, 'behavior_score' => 30, 'engagement_score' => 35, 'overall_risk' => 80,
    ]);

    $first  = $this->service->generateFromRisk($this->school->id);
    $second = $this->service->generateFromRisk($this->school->id);

    expect($first)->toBe(1);
    expect($second)->toBe(0);

    $rec = AiRecommendation::first();
    expect($rec->risk_level)->toBe('high');
    expect($rec->actions)->not->toBeEmpty();
});

it('skips low risk students', function () {
    StudentRiskScore::create([
        'school_id' => $this->school->id, 'student_id' => $this->student->id,
        'snapshot_date' => now()->toDateString(), 'risk_level' => 'low',
        'attendance_score' => 90, 'academic_score' => 90, 'behavior_score' => 90, 'engagement_score' => 90, 'overall_risk' => 10,
    ]);

    expect($this->service->generateFromRisk($this->school->id))->toBe(0);
});

it('marks a recommendation as actioned', function () {
    $rec = AiRecommendation::create([
        'school_id' => $this->school->id, 'student_id' => $this->student->id,
        'type' => 'student_risk', 'risk_level' => 'high', 'actions' => ['Hubungi orang tua'], 'status' => 'pending',
    ]);

    $actioned = $this->service->action($rec, $this->student->user_id, 'Sudah dihubungi');

    expect($actioned->status)->toBe('actioned');
    expect($actioned->reviewed_by)->toBe($this->student->user_id);
});
