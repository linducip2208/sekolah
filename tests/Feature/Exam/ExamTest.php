<?php

use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Exam;
use App\Models\Academic\ExamQuestion;
use App\Models\Academic\ExamResult;
use App\Models\Academic\Medium;
use App\Models\Academic\Section;
use App\Models\Academic\Student;
use App\Models\Academic\Subject;
use App\Models\School;
use App\Models\User;
use App\Services\Academic\ExamService;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->school = School::factory()->create(['settings' => []]);
    app()->instance('current_school', $this->school);

    $this->teacher = User::factory()->create(['school_id' => $this->school->id, 'is_active' => true]);
    $this->teacher->assignRole('teacher');

    $academicYear = AcademicYear::create([
        'school_id'  => $this->school->id,
        'name'       => '2024/2025',
        'start_date' => '2024-07-01',
        'end_date'   => '2025-06-30',
        'is_active'  => true,
    ]);

    $medium    = Medium::create(['school_id' => $this->school->id, 'name' => 'Indonesia']);
    $classRoom = ClassRoom::create(['school_id' => $this->school->id, 'medium_id' => $medium->id, 'name' => 'Kelas 10']);
    $section   = Section::create(['school_id' => $this->school->id, 'name' => 'A']);

    $this->classSection = ClassSection::create([
        'school_id'        => $this->school->id,
        'class_room_id'    => $classRoom->id,
        'section_id'       => $section->id,
        'medium_id'        => $medium->id,
        'academic_year_id' => $academicYear->id,
        'class_teacher_id' => $this->teacher->id,
    ]);

    $this->subject = Subject::create([
        'school_id' => $this->school->id,
        'medium_id' => $medium->id,
        'name'      => 'Matematika',
        'code'      => 'MTK',
        'type'      => 'theory',
    ]);

    $studentUser       = User::factory()->create(['school_id' => $this->school->id]);
    $this->studentUser = $studentUser;
    $this->student     = Student::create([
        'user_id'          => $studentUser->id,
        'school_id'        => $this->school->id,
        'class_section_id' => $this->classSection->id,
    ]);

    $this->exam = Exam::create([
        'school_id'        => $this->school->id,
        'class_section_id' => $this->classSection->id,
        'subject_id'       => $this->subject->id,
        'title'            => 'Ujian Harian 1',
        'type'             => 'online',
        'start_at'         => now()->subHour(),
        'end_at'           => now()->addHours(2),
        'duration_minutes' => 60,
        'total_marks'      => 10,
        'pass_marks'       => 6,
    ]);
});

test('teacher can create an exam', function () {
    Sanctum::actingAs($this->teacher);

    $response = $this->postJson('/api/v1/exams', [
        'class_section_id' => $this->classSection->id,
        'subject_id'       => $this->subject->id,
        'title'            => 'UTS Ganjil',
        'type'             => 'online',
        'start_at'         => now()->addDay()->toDateTimeString(),
        'end_at'           => now()->addDays(2)->toDateTimeString(),
        'duration_minutes' => 90,
        'total_marks'      => 100,
        'pass_marks'       => 60,
    ]);

    $response->assertStatus(201)->assertJsonPath('title', 'UTS Ganjil');
});

test('teacher can add questions to exam', function () {
    Sanctum::actingAs($this->teacher);

    $response = $this->postJson("/api/v1/exams/{$this->exam->id}/questions", [
        'question'       => '2 + 2 = ?',
        'type'           => 'mcq',
        'options'        => [['text' => '3', 'is_correct' => false], ['text' => '4', 'is_correct' => true]],
        'correct_answer' => '4',
        'marks'          => 2,
    ]);

    $response->assertStatus(201);
    expect(ExamQuestion::count())->toBe(1);
});

test('student can start exam within time window', function () {
    Sanctum::actingAs($this->studentUser);

    $response = $this->getJson("/api/v1/exams/{$this->exam->id}/start");

    $response->assertOk();
    expect(ExamResult::count())->toBe(1);
});

test('student cannot start exam before start time', function () {
    Sanctum::actingAs($this->studentUser);

    $this->exam->update(['start_at' => now()->addHours(2)]);

    $response = $this->getJson("/api/v1/exams/{$this->exam->id}/start");
    $response->assertStatus(422);
});

test('auto grade grades mcq correctly', function () {
    Sanctum::actingAs($this->studentUser);

    ExamQuestion::create([
        'exam_id'        => $this->exam->id,
        'question'       => '2 + 2 = ?',
        'type'           => 'mcq',
        'correct_answer' => '4',
        'marks'          => 5,
        'options'        => [['text' => '4', 'is_correct' => true]],
    ]);

    $q2 = ExamQuestion::create([
        'exam_id'        => $this->exam->id,
        'question'       => '5 + 5 = ?',
        'type'           => 'mcq',
        'correct_answer' => '10',
        'marks'          => 5,
        'options'        => [['text' => '10', 'is_correct' => true]],
    ]);

    $result = ExamResult::create([
        'exam_id'    => $this->exam->id,
        'student_id' => $this->student->id,
        'status'     => 'pending',
        'started_at' => now(),
    ]);

    $service = app(ExamService::class);
    $exam    = $this->exam->load('questions');

    // Student answers first correctly, second incorrectly
    $result->update(['answers' => [$exam->questions[0]->id => '4', $q2->id => 'wrong']]);

    $service->autoGrade($result, $exam);

    $result->refresh();
    expect($result->obtained_marks)->toBe(5)
        ->and($result->status)->toBe('failed'); // 5 < 6 (pass_marks)
});

test('student passes when marks meet pass threshold', function () {
    Sanctum::actingAs($this->studentUser);

    $q1 = ExamQuestion::create([
        'exam_id'        => $this->exam->id,
        'question'       => 'Q1',
        'type'           => 'mcq',
        'correct_answer' => 'A',
        'marks'          => 10,
    ]);

    $result = ExamResult::create([
        'exam_id'    => $this->exam->id,
        'student_id' => $this->student->id,
        'status'     => 'pending',
        'started_at' => now(),
        'answers'    => [$q1->id => 'A'],
    ]);

    $service = app(ExamService::class);
    $exam    = $this->exam->load('questions');
    $service->autoGrade($result, $exam);

    $result->refresh();
    expect($result->status)->toBe('passed');
});
