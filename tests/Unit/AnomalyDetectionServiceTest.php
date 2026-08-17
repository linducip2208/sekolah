<?php

use App\Models\Academic\AcademicYear;
use App\Models\Academic\Attendance;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Medium;
use App\Models\Academic\Section;
use App\Models\Academic\Student;
use App\Models\Analytics\AnomalyAlert;
use App\Models\School;
use App\Models\User;
use App\Services\Analytics\AnomalyDetectionService;

beforeEach(function () {
    $this->service = app(AnomalyDetectionService::class);
    $this->school = School::factory()->create();

    $medium = Medium::create(['school_id' => $this->school->id, 'name' => 'Umum']);
    $room = ClassRoom::create(['school_id' => $this->school->id, 'medium_id' => $medium->id, 'name' => '10']);
    $section = Section::create(['school_id' => $this->school->id, 'name' => 'A']);
    $year = AcademicYear::create([
        'school_id' => $this->school->id, 'name' => '2025/2026',
        'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true,
    ]);
    $this->classSection = ClassSection::create([
        'school_id' => $this->school->id, 'class_room_id' => $room->id,
        'section_id' => $section->id, 'medium_id' => $medium->id, 'academic_year_id' => $year->id,
    ]);

    $user = User::factory()->create(['school_id' => $this->school->id]);
    $this->user = $user;
    $this->student = Student::create([
        'user_id' => $user->id, 'school_id' => $this->school->id,
        'class_section_id' => $this->classSection->id, 'admission_no' => 'A-1',
    ]);
});

function anomalyAttendance(int $schoolId, int $studentId, int $classSectionId, int $markerId, string $date, string $status): void
{
    Attendance::create([
        'school_id' => $schoolId, 'student_id' => $studentId,
        'class_section_id' => $classSectionId, 'marked_by' => $markerId,
        'date' => $date, 'status' => $status,
    ]);
}

it('detects an attendance drop between periods', function () {
    // Previous 7 days: mostly present
    for ($d = 14; $d >= 8; $d--) {
        anomalyAttendance($this->school->id, $this->student->id, $this->classSection->id, $this->user->id, now()->subDays($d)->toDateString(), 'present');
    }

    // Recent 7 days: mostly absent
    for ($d = 7; $d >= 1; $d--) {
        anomalyAttendance($this->school->id, $this->student->id, $this->classSection->id, $this->user->id, now()->subDays($d)->toDateString(), 'absent');
    }

    $alerts = $this->service->detectAttendanceDrops($this->school->id);

    expect($alerts)->toHaveCount(1);
    expect($alerts[0]['type'])->toBe('attendance_drop');
});

it('persists new alerts without duplicates on run', function () {
    for ($d = 14; $d >= 8; $d--) {
        anomalyAttendance($this->school->id, $this->student->id, $this->classSection->id, $this->user->id, now()->subDays($d)->toDateString(), 'present');
    }
    for ($d = 7; $d >= 1; $d--) {
        anomalyAttendance($this->school->id, $this->student->id, $this->classSection->id, $this->user->id, now()->subDays($d)->toDateString(), 'absent');
    }

    $first  = $this->service->run($this->school->id);
    $second = $this->service->run($this->school->id);

    expect($first)->toBe(1);
    expect($second)->toBe(0);
    expect(AnomalyAlert::where('school_id', $this->school->id)->count())->toBe(1);
});
