<?php

use App\Models\Academic\AcademicYear;
use App\Models\Academic\Assignment;
use App\Models\Academic\AssignmentSubmission;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Lesson;
use App\Models\Academic\Medium;
use App\Models\Academic\Section;
use App\Models\Academic\Student;
use App\Models\Academic\Subject;
use App\Models\School;
use App\Models\User;
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

    $studentUser    = User::factory()->create(['school_id' => $this->school->id]);
    $this->student  = Student::create([
        'user_id'          => $studentUser->id,
        'school_id'        => $this->school->id,
        'class_section_id' => $this->classSection->id,
    ]);
    $this->studentUser = $studentUser;
});

test('teacher can create a lesson', function () {
    Sanctum::actingAs($this->teacher);

    $response = $this->postJson('/api/v1/classroom/lessons', [
        'class_section_id' => $this->classSection->id,
        'subject_id'       => $this->subject->id,
        'title'            => 'Bab 1 - Himpunan',
        'description'      => 'Pengantar teori himpunan',
    ]);

    $response->assertStatus(201)->assertJsonPath('title', 'Bab 1 - Himpunan');
    expect(Lesson::count())->toBe(1);
});

test('teacher can create an assignment', function () {
    Sanctum::actingAs($this->teacher);

    $lesson = Lesson::create([
        'school_id'        => $this->school->id,
        'class_section_id' => $this->classSection->id,
        'subject_id'       => $this->subject->id,
        'teacher_id'       => $this->teacher->id,
        'title'            => 'Bab 1',
    ]);

    $response = $this->postJson('/api/v1/classroom/assignments', [
        'lesson_id'    => $lesson->id,
        'title'        => 'Latihan 1',
        'instructions' => 'Kerjakan soal berikut',
        'due_date'     => now()->addDays(7)->toDateTimeString(),
        'total_marks'  => 100,
    ]);

    $response->assertStatus(201)->assertJsonPath('title', 'Latihan 1');
});

test('student can submit assignment', function () {
    Sanctum::actingAs($this->studentUser);

    $lesson = Lesson::create([
        'school_id'        => $this->school->id,
        'class_section_id' => $this->classSection->id,
        'subject_id'       => $this->subject->id,
        'teacher_id'       => $this->teacher->id,
        'title'            => 'Bab 1',
    ]);

    $assignment = Assignment::create([
        'school_id'   => $this->school->id,
        'lesson_id'   => $lesson->id,
        'title'       => 'Latihan 1',
        'due_date'    => now()->addDays(7),
        'total_marks' => 100,
    ]);

    $response = $this->postJson("/api/v1/classroom/assignments/{$assignment->id}/submit", [
        'answer' => 'Jawaban saya',
    ]);

    $response->assertStatus(201);
    $submission = AssignmentSubmission::first();
    expect($submission->is_late)->toBeFalse()
        ->and($submission->student_id)->toBe($this->student->id);
});

test('late submission is marked as late', function () {
    Sanctum::actingAs($this->studentUser);

    $lesson = Lesson::create([
        'school_id'        => $this->school->id,
        'class_section_id' => $this->classSection->id,
        'subject_id'       => $this->subject->id,
        'teacher_id'       => $this->teacher->id,
        'title'            => 'Bab 1',
    ]);

    $assignment = Assignment::create([
        'school_id'   => $this->school->id,
        'lesson_id'   => $lesson->id,
        'title'       => 'Latihan Lama',
        'due_date'    => now()->subDays(2), // already past
        'total_marks' => 100,
    ]);

    $this->postJson("/api/v1/classroom/assignments/{$assignment->id}/submit", [
        'answer' => 'Terlambat',
    ]);

    expect(AssignmentSubmission::first()->is_late)->toBeTrue();
});

test('teacher can grade a submission', function () {
    Sanctum::actingAs($this->teacher);

    $lesson = Lesson::create([
        'school_id'        => $this->school->id,
        'class_section_id' => $this->classSection->id,
        'subject_id'       => $this->subject->id,
        'teacher_id'       => $this->teacher->id,
        'title'            => 'Bab 1',
    ]);

    $assignment = Assignment::create([
        'school_id'   => $this->school->id,
        'lesson_id'   => $lesson->id,
        'title'       => 'Latihan 1',
        'due_date'    => now()->addDays(7),
        'total_marks' => 100,
    ]);

    $submission = AssignmentSubmission::create([
        'assignment_id' => $assignment->id,
        'student_id'    => $this->student->id,
        'answer'        => 'Jawaban',
        'is_late'       => false,
    ]);

    $response = $this->postJson("/api/v1/classroom/submissions/{$submission->id}/grade", [
        'marks'    => 85,
        'feedback' => 'Bagus sekali',
    ]);

    $response->assertOk()->assertJsonPath('marks', 85);
});

test('grade cannot exceed total marks', function () {
    Sanctum::actingAs($this->teacher);

    $lesson = Lesson::create([
        'school_id'        => $this->school->id,
        'class_section_id' => $this->classSection->id,
        'subject_id'       => $this->subject->id,
        'teacher_id'       => $this->teacher->id,
        'title'            => 'Bab 1',
    ]);

    $assignment = Assignment::create([
        'school_id'   => $this->school->id,
        'lesson_id'   => $lesson->id,
        'title'       => 'Latihan 1',
        'due_date'    => now()->addDays(7),
        'total_marks' => 100,
    ]);

    $submission = AssignmentSubmission::create([
        'assignment_id' => $assignment->id,
        'student_id'    => $this->student->id,
        'answer'        => 'Jawaban',
        'is_late'       => false,
    ]);

    $response = $this->postJson("/api/v1/classroom/submissions/{$submission->id}/grade", [
        'marks'    => 150,
        'feedback' => 'Terlalu besar',
    ]);

    $response->assertStatus(422);
});
