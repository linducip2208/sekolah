<?php

namespace Tests\Feature;

use App\Models\Academic\Student;
use App\Models\School;
use App\Models\User;
use App\Services\Analytics\RiskScoreService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

class RiskScoreSafetyTest extends TestCase
{
    public function test_risk_score_marks_missing_academic_data_instead_of_using_fake_average(): void
    {
        $school = School::factory()->create();
        $student = Student::create([
            'school_id' => $school->id,
            'user_id' => User::factory()->create(['school_id' => $school->id])->id,
            'status' => 'active',
        ]);

        $score = app(RiskScoreService::class)->computeForStudent($school->id, $student->id);

        $this->assertContains('insufficient_academic_data', $score->top_risk_factors);
        $this->assertSame(0.0, (float) $score->academic_score);
    }

    public function test_risk_score_rejects_a_student_from_another_school(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $studentB = Student::create([
            'school_id' => $schoolB->id,
            'user_id' => User::factory()->create(['school_id' => $schoolB->id])->id,
            'status' => 'active',
        ]);

        $this->expectException(ModelNotFoundException::class);
        app(RiskScoreService::class)->computeForStudent($schoolA->id, $studentB->id);
    }
}
