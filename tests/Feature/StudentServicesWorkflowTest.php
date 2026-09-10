<?php

namespace Tests\Feature;

use App\Models\Academic\Student;
use App\Models\Discipline\DisciplineCategory;
use App\Models\School;
use App\Models\User;
use App\Services\Counseling\CounselingService;
use App\Services\Discipline\DisciplineService;
use App\Services\Medical\ClinicService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class StudentServicesWorkflowTest extends TestCase
{
    public function test_discipline_enforces_student_tenant_and_configured_threshold(): void
    {
        Bus::fake();
        [$school, $student, $reporter] = $this->schoolStudent('discipline-a');
        [, $foreignStudent] = $this->schoolStudent('discipline-b');
        $category = DisciplineCategory::create([
            'school_id' => $school->id,
            'name' => 'Pelanggaran serius',
            'type' => 'violation',
            'point_value' => 10,
            'auto_sanction' => true,
            'sanction_thresholds' => [['at_points' => 10, 'action' => 'Panggilan wali']],
        ]);
        $service = app(DisciplineService::class);

        $this->expectException(ModelNotFoundException::class);
        $service->record($school->id, $foreignStudent->id, $category->id, $reporter->id, [
            'description' => 'Data lintas sekolah tidak boleh diterima.',
        ]);
    }

    public function test_discipline_threshold_transitions_to_sanctioned(): void
    {
        Bus::fake();
        [$school, $student, $reporter] = $this->schoolStudent('discipline-threshold');
        $category = DisciplineCategory::create([
            'school_id' => $school->id,
            'name' => 'Terlambat berulang',
            'type' => 'violation',
            'point_value' => 10,
            'auto_sanction' => true,
            'sanction_thresholds' => [['at_points' => 10, 'action' => 'Pembinaan']],
        ]);

        $record = app(DisciplineService::class)->record($school->id, $student->id, $category->id, $reporter->id, [
            'description' => 'Pelanggaran tercatat.',
        ]);

        $this->assertSame('sanctioned', $record->status);
        $this->assertSame('Pembinaan', $record->sanction_applied);
    }

    public function test_counseling_rejects_foreign_student_at_service_boundary(): void
    {
        [$school, $student, $counselor] = $this->schoolStudent('counseling-a');
        [, $foreignStudent] = $this->schoolStudent('counseling-b');
        $service = app(CounselingService::class);
        $scheduledAt = now()->addDay()->setTime(9, 0);

        $this->expectException(ModelNotFoundException::class);
        $service->scheduleSession($school->id, [
            'student_id' => $foreignStudent->id,
            'counselor_id' => $counselor->id,
            'scheduled_at' => $scheduledAt,
            'type' => 'academic',
        ]);

    }

    public function test_counseling_prevents_overlapping_sessions_and_repeated_completion(): void
    {
        [$school, $student, $counselor] = $this->schoolStudent('counseling-overlap');
        $service = app(CounselingService::class);
        $scheduledAt = now()->addDay()->setTime(9, 0);
        $session = $service->scheduleSession($school->id, [
            'student_id' => $student->id,
            'counselor_id' => $counselor->id,
            'scheduled_at' => $scheduledAt,
            'type' => 'academic',
        ]);
        $this->expectException(HttpException::class);
        $service->scheduleSession($school->id, [
            'student_id' => $student->id,
            'counselor_id' => $counselor->id,
            'scheduled_at' => $scheduledAt->copy()->addMinutes(15),
            'type' => 'behavior',
        ]);
    }

    public function test_counseling_session_cannot_be_completed_twice(): void
    {
        [$school, $student, $counselor] = $this->schoolStudent('counseling-complete');
        $service = app(CounselingService::class);
        $session = $service->scheduleSession($school->id, [
            'student_id' => $student->id,
            'counselor_id' => $counselor->id,
            'scheduled_at' => now()->addDay()->setTime(10, 0),
            'type' => 'academic',
        ]);

        $service->completeSession($session, 'Sesi selesai.');
        $this->expectException(HttpException::class);
        $service->completeSession($session, 'Tidak boleh diselesaikan dua kali.');
    }

    public function test_clinic_rejects_foreign_students_at_service_boundary(): void
    {
        [$school, , $nurse] = $this->schoolStudent('clinic-a');
        [, $foreignStudent] = $this->schoolStudent('clinic-b');
        $service = app(ClinicService::class);

        $this->expectException(ModelNotFoundException::class);
        $service->recordVisit($school->id, $foreignStudent->id, $nurse->id, [
            'symptoms' => 'Tidak valid lintas sekolah.',
        ]);
    }

    /** @return array{0: School, 1: Student, 2: User} */
    private function schoolStudent(string $suffix): array
    {
        $school = School::factory()->create();
        $user = User::factory()->create(['school_id' => $school->id]);
        $student = Student::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        return [$school, $student, $user];
    }
}
