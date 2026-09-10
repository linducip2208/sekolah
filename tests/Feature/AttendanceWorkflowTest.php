<?php

namespace Tests\Feature;

use App\Jobs\NotifyAbsenceJob;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Attendance;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Medium;
use App\Models\Academic\Section;
use App\Models\Academic\Student;
use App\Models\Plan;
use App\Models\School;
use App\Models\User;
use App\Services\Academic\AttendanceService;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AttendanceWorkflowTest extends TestCase
{
    public function test_bulk_mark_rejects_student_from_another_school(): void
    {
        [$schoolA, $classA, $teacherA] = $this->schoolClassAndTeacher('A');
        [$schoolB, $classB] = $this->schoolClassAndTeacher('B');
        $studentB = $this->student($schoolB, $classB, 'B-1');

        $this->expectException(HttpException::class);

        app(AttendanceService::class)->bulkMark(
            $classA->id,
            today()->toDateString(),
            [['student_id' => $studentB->id, 'status' => 'absent']],
            $teacherA,
        );

        $this->assertDatabaseMissing('attendances', ['student_id' => $studentB->id]);
    }

    public function test_bulk_mark_dispatches_absence_notification_and_persists_real_students(): void
    {
        Bus::fake();
        [$school, $classSection, $teacher] = $this->schoolClassAndTeacher('notify');
        $student = $this->student($school, $classSection, 'N-1');

        $summary = app(AttendanceService::class)->bulkMark(
            $classSection->id,
            today()->toDateString(),
            [['student_id' => $student->id, 'status' => 'absent', 'note' => 'Tidak hadir']],
            $teacher,
        );

        $this->assertSame(1, $summary['absent']);
        $this->assertDatabaseHas('attendances', [
            'school_id' => $school->id,
            'student_id' => $student->id,
            'class_section_id' => $classSection->id,
            'status' => 'absent',
        ]);
        Bus::assertDispatched(NotifyAbsenceJob::class);
    }

    public function test_authenticated_api_cannot_read_another_school_class(): void
    {
        [$schoolA, , $teacherA] = $this->schoolClassAndTeacher('api-a');
        [$schoolB, $classB] = $this->schoolClassAndTeacher('api-b');

        $response = $this->actingAs($teacherA, 'sanctum')
            ->getJson('/api/v1/attendance/class/'.$classB->id.'?date='.today()->toDateString());

        $response->assertNotFound();
        $this->assertNotSame($schoolA->id, $schoolB->id);
    }

    public function test_locked_attendance_requires_approved_correction_workflow(): void
    {
        [$school, $classSection, $teacher] = $this->schoolClassAndTeacher('lock');
        $student = $this->student($school, $classSection, 'L-1');
        $date = today()->toDateString();
        $service = app(AttendanceService::class);

        $service->bulkMark($classSection->id, $date, [
            ['student_id' => $student->id, 'status' => 'present'],
        ], $teacher);
        $service->lockDate($classSection->id, $date, $teacher);

        try {
            $service->bulkMark($classSection->id, $date, [
                ['student_id' => $student->id, 'status' => 'absent'],
            ], $teacher);
            $this->fail('Locked attendance must reject direct writes.');
        } catch (HttpException $exception) {
            $this->assertSame(423, $exception->getStatusCode());
        }

        $workflow = $service->requestCorrection(
            Attendance::where('school_id', $school->id)->where('student_id', $student->id)->firstOrFail(),
            ['status' => 'absent', 'reason' => 'Siswa hadir setelah rekap awal dikunci.'],
            $teacher,
        );

        $approver = User::factory()->create(['school_id' => $school->id]);
        $approver->assignRole('admin');
        $response = $this->actingAs($approver, 'sanctum')
            ->postJson('/api/v1/attendance/corrections/'.$workflow->id.'/approve', ['note' => 'Diverifikasi wali kelas.']);

        $response->assertOk();
        $this->assertDatabaseHas('workflow_requests', ['id' => $workflow->id, 'status' => 'approved']);
        $this->assertDatabaseHas('attendances', [
            'id' => $workflow->payload['attendance_id'],
            'status' => 'absent',
        ]);
    }

    /** @return array{0:School,1:ClassSection,2:User} */
    private function schoolClassAndTeacher(string $suffix): array
    {
        $school = School::factory()->create(['plan_id' => Plan::factory()->create()->id]);
        $medium = Medium::create(['school_id' => $school->id, 'name' => 'Umum '.$suffix]);
        $room = ClassRoom::create(['school_id' => $school->id, 'medium_id' => $medium->id, 'name' => 'X']);
        $section = Section::create(['school_id' => $school->id, 'name' => 'A']);
        $year = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2025/2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);
        $classSection = ClassSection::create([
            'school_id' => $school->id,
            'class_room_id' => $room->id,
            'section_id' => $section->id,
            'medium_id' => $medium->id,
            'academic_year_id' => $year->id,
        ]);

        $teacher = User::factory()->create(['school_id' => $school->id]);
        $teacher->assignRole('teacher');
        $classSection->update(['class_teacher_id' => $teacher->id]);

        return [$school, $classSection->fresh(), $teacher];
    }

    private function student(School $school, ClassSection $classSection, string $admissionNo): Student
    {
        $user = User::factory()->create(['school_id' => $school->id]);

        return Student::create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'class_section_id' => $classSection->id,
            'admission_no' => $admissionNo,
            'status' => 'active',
        ]);
    }
}
