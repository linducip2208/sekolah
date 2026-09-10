<?php

use App\Mail\PpdbAcceptanceMail;
use App\Mail\PpdbSubmissionMail;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Medium;
use App\Models\Academic\Section;
use App\Models\Academic\Student;
use App\Models\PPDB\PpdbApplication;
use App\Models\PPDB\PpdbPeriod;
use App\Models\School;
use App\Models\User;
use App\Services\PPDB\PpdbService;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    $this->service = app(PpdbService::class);
    $this->school = School::factory()->create();

    $year = AcademicYear::create([
        'school_id' => $this->school->id, 'name' => '2025/2026',
        'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true,
    ]);
    $this->period = PpdbPeriod::create([
        'school_id' => $this->school->id, 'academic_year_id' => $year->id, 'name' => 'PPDB 2026',
        'open_date' => '2025-06-01', 'close_date' => '2025-07-01', 'is_published' => true,
    ]);

    $medium = Medium::create(['school_id' => $this->school->id, 'name' => 'Umum']);
    $room = ClassRoom::create(['school_id' => $this->school->id, 'medium_id' => $medium->id, 'name' => '10']);
    $section = Section::create(['school_id' => $this->school->id, 'name' => 'A']);
    $this->classSection = ClassSection::create([
        'school_id' => $this->school->id, 'class_room_id' => $room->id,
        'section_id' => $section->id, 'medium_id' => $medium->id, 'academic_year_id' => $year->id,
    ]);

    Role::firstOrCreate(['name' => 'student']);

    $this->application = PpdbApplication::create([
        'school_id' => $this->school->id, 'ppdb_period_id' => $this->period->id,
        'registration_no' => 'PPDB-1', 'jalur' => 'reguler',
        'student_name' => 'Budi Santoso', 'date_of_birth' => '2010-05-01', 'gender' => 'male',
        'address' => 'Jakarta', 'district' => 'Jakarta Selatan', 'city' => 'Jakarta',
        'parent_name' => 'Orang Tua Budi', 'parent_phone' => '08123', 'parent_email' => 'ortu@example.com',
        'average_score' => 85, 'entrance_test_score' => 90, 'interview_score' => 80,
        'status' => 'accepted',
    ]);
});

it('converts an accepted applicant into an enrolled student', function () {
    $student = $this->service->enrollStudent($this->application, $this->classSection->id);

    expect($student)->toBeInstanceOf(Student::class);
    expect($student->status)->toBe('enrolled');
    expect($student->user)->not->toBeNull();
    expect($student->user->hasRole('student'))->toBeTrue();

    expect($this->application->fresh()->status)->toBe('enrolled');
    expect($this->application->fresh()->enrolled_student_id)->toBe($student->id);
});

it('refuses to enroll an application that is not accepted', function () {
    $this->application->update(['status' => 'verified']);

    $this->expectException(HttpException::class);
    $this->service->enrollStudent($this->application->fresh(), $this->classSection->id);
});

it('includes test and interview scores in ranking', function () {
    $this->period->update(['jalur_config' => ['reguler' => 10]]);
    $this->application->update(['status' => 'verified']);

    $result = $this->service->runSelection($this->period);

    $app = $this->application->fresh();
    expect($app->ranking_score)->not->toBeNull();
    expect($result['accepted_total'])->toBeGreaterThanOrEqual(1);
});

it('does not batch enroll an applicant from another school', function () {
    $otherSchool = School::factory()->create();
    $otherYear = AcademicYear::create([
        'school_id' => $otherSchool->id, 'name' => '2025/2026',
        'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true,
    ]);
    $otherPeriod = PpdbPeriod::create([
        'school_id' => $otherSchool->id, 'academic_year_id' => $otherYear->id, 'name' => 'PPDB Other',
        'open_date' => '2025-06-01', 'close_date' => '2025-07-01', 'is_published' => true,
    ]);
    $otherApplication = PpdbApplication::create([
        'school_id' => $otherSchool->id, 'ppdb_period_id' => $otherPeriod->id,
        'registration_no' => 'PPDB-OTHER', 'jalur' => 'reguler',
        'student_name' => 'Siswa Sekolah Lain', 'date_of_birth' => '2010-05-01', 'gender' => 'male',
        'address' => 'Jakarta', 'district' => 'Jakarta Selatan', 'city' => 'Jakarta',
        'parent_name' => 'Orang Tua', 'parent_phone' => '08123', 'parent_email' => 'other@example.com',
        'status' => 'accepted',
    ]);

    $result = $this->service->batchEnroll(
        [$otherApplication->id],
        $this->classSection->id,
        999,
        $this->school->id,
    );

    expect($result)->toBe(['enrolled' => 0, 'failed' => [$otherApplication->id]]);
    expect($otherApplication->fresh()->enrolled_student_id)->toBeNull();
});

it('enforces the PPDB lifecycle and rejects duplicate NISN', function () {
    Mail::fake();
    $this->period->update([
        'open_date' => today(),
        'close_date' => today()->addDays(5),
        'jalur_config' => ['reguler' => ['quota' => 2]],
    ]);

    $data = [
        'jalur' => 'reguler',
        'student_name' => 'Siti Validasi',
        'nisn' => '0099887766',
        'date_of_birth' => '2010-06-01',
        'gender' => 'female',
        'address' => 'Jakarta',
        'district' => 'Jakarta Selatan',
        'city' => 'Jakarta',
        'parent_name' => 'Wali Siti',
        'parent_phone' => '08124',
        'parent_email' => 'siti@example.com',
    ];

    $application = $this->service->register($this->period, $data);
    $reviewer = User::factory()->create(['school_id' => $this->school->id]);
    expect($application->status)->toBe('draft');
    expect(fn () => $this->service->verify($application, $reviewer->id))->toThrow(HttpException::class);

    $submitted = $this->service->submit($application);
    $verified = $this->service->verify($submitted, $reviewer->id);
    $accepted = $this->service->accept($verified, $reviewer->id);
    expect($accepted->status)->toBe('accepted');
    Mail::assertQueued(PpdbSubmissionMail::class);
    Mail::assertQueued(PpdbAcceptanceMail::class);

    expect(fn () => $this->service->register($this->period, $data))->toThrow(HttpException::class);
});

it('assigns verified applicants to accepted and waitlist positions by quota', function () {
    $this->period->update(['jalur_config' => ['reguler' => ['quota' => 1]]]);

    $first = $this->application->fresh();
    $first->update(['status' => 'verified', 'average_score' => 95]);
    $second = PpdbApplication::create([
        'school_id' => $this->school->id,
        'ppdb_period_id' => $this->period->id,
        'registration_no' => 'PPDB-2',
        'jalur' => 'reguler',
        'student_name' => 'Peserta Kedua',
        'date_of_birth' => '2010-06-02',
        'gender' => 'female',
        'address' => 'Jakarta',
        'district' => 'Jakarta Selatan',
        'city' => 'Jakarta',
        'parent_name' => 'Wali Kedua',
        'parent_phone' => '08125',
        'parent_email' => 'kedua@example.com',
        'average_score' => 70,
        'status' => 'verified',
    ]);

    $result = $this->service->runSelection($this->period);

    expect($result['accepted_total'])->toBe(1)
        ->and($result['waitlist_total'])->toBe(1);
    expect($first->fresh()->status)->toBe('accepted');
    expect($second->fresh()->status)->toBe('waitlist')
        ->and($second->fresh()->waiting_list_position)->toBe(1);
});
