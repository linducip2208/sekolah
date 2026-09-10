<?php

namespace Tests\Feature;

use App\Models\Academic\Student;
use App\Models\School;
use App\Models\User;
use App\Services\Religious\ReligiousService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

class ReligiousTenantSafetyTest extends TestCase
{
    public function test_religious_progress_rejects_a_student_from_another_school(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $actor = User::factory()->create(['school_id' => $schoolA->id]);
        $foreignUser = User::factory()->create(['school_id' => $schoolB->id]);
        $foreignStudent = Student::create([
            'school_id' => $schoolB->id,
            'user_id' => $foreignUser->id,
        ]);

        $this->actingAs($actor, 'sanctum');
        $this->expectException(ModelNotFoundException::class);

        app(ReligiousService::class)->recordHafalan($schoolA->id, $foreignStudent->id, $actor->id, [
            'surah' => 'Al-Fatihah',
            'ayah_start' => 1,
            'ayah_end' => 7,
        ]);
    }

    public function test_religious_summary_does_not_return_cross_school_data(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $actor = User::factory()->create(['school_id' => $schoolA->id]);
        $foreignUser = User::factory()->create(['school_id' => $schoolB->id]);
        $foreignStudent = Student::create([
            'school_id' => $schoolB->id,
            'user_id' => $foreignUser->id,
        ]);

        $this->actingAs($actor, 'sanctum');
        $this->expectException(ModelNotFoundException::class);

        app(ReligiousService::class)->studentHafalanSummary($schoolA->id, $foreignStudent->id);
    }
}
