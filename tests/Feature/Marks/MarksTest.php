<?php

use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\GradeRule;
use App\Models\Academic\GradeSystem;
use App\Models\Academic\Mark;
use App\Models\Academic\Medium;
use App\Models\Academic\ReportCard;
use App\Models\Academic\Section;
use App\Models\Academic\Semester;
use App\Models\Academic\Student;
use App\Models\Academic\Subject;
use App\Models\School;
use App\Models\User;
use App\Services\Academic\MarksService;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->school = School::factory()->create(['settings' => []]);
    app()->instance('current_school', $this->school);

    $this->admin = User::factory()->create(['school_id' => $this->school->id, 'is_active' => true]);
    $this->admin->assignRole('admin');

    $academicYear = AcademicYear::create([
        'school_id'  => $this->school->id,
        'name'       => '2024/2025',
        'start_date' => '2024-07-01',
        'end_date'   => '2025-06-30',
        'is_active'  => true,
    ]);

    $this->semester = Semester::create([
        'school_id'       => $this->school->id,
        'academic_year_id'=> $academicYear->id,
        'name'            => 'Semester 1',
        'start_date'      => '2024-07-01',
        'end_date'        => '2024-12-31',
        'is_active'       => true,
    ]);

    $medium    = Medium::create(['school_id' => $this->school->id, 'name' => 'Indonesia']);
    $classRoom = ClassRoom::create(['school_id' => $this->school->id, 'medium_id' => $medium->id, 'name' => 'Kelas 10']);
    $section   = Section::create(['school_id' => $this->school->id, 'name' => 'A']);

    $teacher = User::factory()->create(['school_id' => $this->school->id]);
    $this->classSection = ClassSection::create([
        'school_id'        => $this->school->id,
        'class_room_id'    => $classRoom->id,
        'section_id'       => $section->id,
        'medium_id'        => $medium->id,
        'academic_year_id' => $academicYear->id,
        'class_teacher_id' => $teacher->id,
    ]);

    $this->subject = Subject::create([
        'school_id' => $this->school->id,
        'medium_id' => $medium->id,
        'name'      => 'Matematika',
        'code'      => 'MTK',
        'type'      => 'theory',
    ]);

    $studentUser    = User::factory()->create(['school_id' => $this->school->id]);
    $this->student  = Student::create([
        'user_id'          => $studentUser->id,
        'school_id'        => $this->school->id,
        'class_section_id' => $this->classSection->id,
    ]);

    $this->gradeSystem = GradeSystem::create([
        'school_id' => $this->school->id,
        'name'      => 'Sistem Nilai SMA',
        'is_active' => true,
    ]);

    GradeRule::create(['grade_system_id' => $this->gradeSystem->id, 'grade' => 'A', 'min_percent' => 85, 'max_percent' => 100, 'gpa_point' => 4.00]);
    GradeRule::create(['grade_system_id' => $this->gradeSystem->id, 'grade' => 'B', 'min_percent' => 70, 'max_percent' => 84, 'gpa_point' => 3.00]);
    GradeRule::create(['grade_system_id' => $this->gradeSystem->id, 'grade' => 'C', 'min_percent' => 55, 'max_percent' => 69, 'gpa_point' => 2.00]);
    GradeRule::create(['grade_system_id' => $this->gradeSystem->id, 'grade' => 'F', 'min_percent' => 0,  'max_percent' => 54, 'gpa_point' => 0.00]);
});

test('admin can bulk input marks', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->postJson('/api/v1/marks/bulk', [
        'marks' => [
            [
                'student_id'     => $this->student->id,
                'subject_id'     => $this->subject->id,
                'semester_id'    => $this->semester->id,
                'obtained_marks' => 85,
                'total_marks'    => 100,
            ],
        ],
    ]);

    $response->assertOk()->assertJsonPath('saved', 1);
    expect(Mark::count())->toBe(1);
});

test('grade is resolved correctly from grade system', function () {
    Sanctum::actingAs($this->admin);

    $service = app(MarksService::class);

    expect($service->resolveGrade($this->school->id, 90))->toBe('A')
        ->and($service->resolveGrade($this->school->id, 75))->toBe('B')
        ->and($service->resolveGrade($this->school->id, 40))->toBe('F');
});

test('marks store grade automatically on bulk save', function () {
    Sanctum::actingAs($this->admin);

    $this->postJson('/api/v1/marks/bulk', [
        'marks' => [[
            'student_id'     => $this->student->id,
            'subject_id'     => $this->subject->id,
            'semester_id'    => $this->semester->id,
            'obtained_marks' => 90,
            'total_marks'    => 100,
        ]],
    ]);

    $mark = Mark::first();
    expect($mark->grade)->toBe('A')
        ->and((float) $mark->percentage)->toBe(90.0);
});

test('report cards are generated with ranking', function () {
    Sanctum::actingAs($this->admin);

    // Create a second student
    $user2    = User::factory()->create(['school_id' => $this->school->id]);
    $student2 = Student::create([
        'user_id'          => $user2->id,
        'school_id'        => $this->school->id,
        'class_section_id' => $this->classSection->id,
    ]);

    // Add marks for both students
    Mark::create([
        'school_id'      => $this->school->id,
        'student_id'     => $this->student->id,
        'subject_id'     => $this->subject->id,
        'semester_id'    => $this->semester->id,
        'obtained_marks' => 90,
        'total_marks'    => 100,
    ]);

    Mark::create([
        'school_id'      => $this->school->id,
        'student_id'     => $student2->id,
        'subject_id'     => $this->subject->id,
        'semester_id'    => $this->semester->id,
        'obtained_marks' => 70,
        'total_marks'    => 100,
    ]);

    $service = app(MarksService::class);
    $count   = $service->generateReportCards($this->semester->id);

    expect($count)->toBe(2);

    $card1 = ReportCard::where('student_id', $this->student->id)->first();
    $card2 = ReportCard::where('student_id', $student2->id)->first();

    expect($card1->rank)->toBe(1)
        ->and($card2->rank)->toBe(2);
});
