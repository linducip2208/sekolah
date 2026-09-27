<?php

use App\Models\Academic\Exam;
use App\Models\Academic\ExamQuestion;
use App\Models\Academic\Semester;
use App\Models\Academic\Student;
use App\Models\Academic\Subject;
use App\Models\Finance\FeeStructure;
use App\Models\Academic\GradeSystem;
use App\Models\Facilities\Book;
use App\Models\Facilities\BookIssue;
use App\Models\School;
use App\Models\User;
use App\Services\Academic\MarksService;
use App\Services\Facilities\LibraryService;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

function examSecBuildSchool(string $suffix): array
{
    $school = School::factory()->create();
    $medium = \App\Models\Academic\Medium::create(['school_id' => $school->id, 'name' => "Umum-{$suffix}"]);
    $room = \App\Models\Academic\ClassRoom::create(['school_id' => $school->id, 'medium_id' => $medium->id, 'name' => '10']);
    $section = \App\Models\Academic\Section::create(['school_id' => $school->id, 'name' => 'A']);
    $year = \App\Models\Academic\AcademicYear::create([
        'school_id' => $school->id, 'name' => '2025/2026',
        'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true,
    ]);
    $classSection = \App\Models\Academic\ClassSection::create([
        'school_id' => $school->id, 'class_room_id' => $room->id,
        'section_id' => $section->id, 'medium_id' => $medium->id,
        'academic_year_id' => $year->id,
    ]);
    $subject = Subject::create(['school_id' => $school->id, 'medium_id' => $medium->id, 'name' => 'Matematika']);

    return [$school, $classSection, $subject, $year];
}

test('student cannot see answer key via questions endpoint', function () {
    [$school, $classSection, $subject] = examSecBuildSchool('A');
    Role::firstOrCreate(['name' => 'student']);

    $exam = Exam::create([
        'school_id' => $school->id, 'class_section_id' => $classSection->id,
        'subject_id' => $subject->id, 'title' => 'UTS', 'type' => 'online',
        'total_marks' => 10, 'pass_marks' => 5,
    ]);
    ExamQuestion::create([
        'school_id' => $school->id, 'exam_id' => $exam->id,
        'question' => '2+2?', 'type' => 'mcq',
        'options' => [['text' => '4']], 'correct_answer' => '4', 'marks' => 10, 'order' => 0,
    ]);

    $studentUser = User::factory()->create(['school_id' => $school->id]);
    $studentUser->assignRole('student');
    Student::create(['user_id' => $studentUser->id, 'school_id' => $school->id, 'class_section_id' => $classSection->id]);

    Sanctum::actingAs($studentUser);
    $response = $this->getJson("/api/v1/exams/{$exam->id}/questions");
    $response->assertOk();
    expect($response->json()[0])->not->toHaveKey('correct_answer');
});

test('teacher can see answer key via questions endpoint', function () {
    [$school, $classSection, $subject] = examSecBuildSchool('B');
    Role::firstOrCreate(['name' => 'teacher']);

    $exam = Exam::create([
        'school_id' => $school->id, 'class_section_id' => $classSection->id,
        'subject_id' => $subject->id, 'title' => 'UTS', 'type' => 'online',
        'total_marks' => 10, 'pass_marks' => 5,
    ]);
    ExamQuestion::create([
        'school_id' => $school->id, 'exam_id' => $exam->id,
        'question' => '2+2?', 'type' => 'mcq',
        'options' => [['text' => '4']], 'correct_answer' => '4', 'marks' => 10, 'order' => 0,
    ]);

    $teacher = User::factory()->create(['school_id' => $school->id]);
    $teacher->assignRole('teacher');

    Sanctum::actingAs($teacher);
    $response = $this->getJson("/api/v1/exams/{$exam->id}/questions");
    $response->assertOk();
    expect($response->json()[0])->toHaveKey('correct_answer');
});

test('cross-school exam update returns 404', function () {
    [$schoolA, $csA, $subA] = examSecBuildSchool('C');
    [$schoolB, $csB, $subB] = examSecBuildSchool('D');

    $examB = Exam::create([
        'school_id' => $schoolB->id, 'class_section_id' => $csB->id,
        'subject_id' => $subB->id, 'title' => 'Milik B', 'type' => 'offline',
    ]);

    $adminA = User::factory()->create(['school_id' => $schoolA->id, 'is_active' => true]);
    $adminA->assignRole('admin');

    Sanctum::actingAs($adminA);
    $this->putJson("/api/v1/exams/{$examB->id}", ['title' => 'Diretas'])->assertNotFound();
    $this->deleteJson("/api/v1/exams/{$examB->id}")->assertNotFound();
    $this->getJson("/api/v1/exams/{$examB->id}/submissions")->assertNotFound();
});

test('student cannot list exam submissions', function () {
    [$school, $classSection, $subject] = examSecBuildSchool('E');
    Role::firstOrCreate(['name' => 'student']);

    $exam = Exam::create([
        'school_id' => $school->id, 'class_section_id' => $classSection->id,
        'subject_id' => $subject->id, 'title' => 'UTS', 'type' => 'online',
    ]);

    $studentUser = User::factory()->create(['school_id' => $school->id]);
    $studentUser->assignRole('student');

    Sanctum::actingAs($studentUser);
    $this->getJson("/api/v1/exams/{$exam->id}/submissions")->assertForbidden();
});

test('exam store rejects cross-school class section', function () {
    [$schoolA] = examSecBuildSchool('F');
    [, $csB, $subB] = examSecBuildSchool('G');

    $adminA = User::factory()->create(['school_id' => $schoolA->id, 'is_active' => true]);
    $adminA->assignRole('admin');

    Sanctum::actingAs($adminA);
    $this->postJson('/api/v1/exams', [
        'class_section_id' => $csB->id, 'subject_id' => $subB->id, 'title' => 'Silang',
    ])->assertStatus(422);
});

test('bulk marks ignore caller-supplied grade when system resolves', function () {
    [$school, $classSection, $subject, $year] = examSecBuildSchool('H');
    Role::firstOrCreate(['name' => 'teacher']);

    $semester = Semester::create([
        'school_id' => $school->id, 'academic_year_id' => $year->id, 'name' => 'Ganjil',
        'start_date' => '2025-07-01', 'end_date' => '2025-12-31', 'is_active' => true,
    ]);
    $system = GradeSystem::create(['school_id' => $school->id, 'name' => 'KTSP', 'is_active' => true]);
    $system->rules()->create(['grade' => 'A', 'min_percent' => 85, 'max_percent' => 100]);
    $system->rules()->create(['grade' => 'E', 'min_percent' => 0, 'max_percent' => 84.99]);

    $teacher = User::factory()->create(['school_id' => $school->id]);
    $teacher->assignRole('teacher');
    $studentUser = User::factory()->create(['school_id' => $school->id]);
    $student = Student::create(['user_id' => $studentUser->id, 'school_id' => $school->id, 'class_section_id' => $classSection->id]);

    Sanctum::actingAs($teacher);
    app(MarksService::class)->bulkSave([[
        'student_id' => $student->id, 'subject_id' => $subject->id,
        'semester_id' => $semester->id, 'obtained_marks' => 95, 'total_marks' => 100,
        'grade' => 'E',
    ]]);

    $this->assertDatabaseHas('marks', [
        'student_id' => $student->id, 'grade' => 'A',
    ]);
});

test('markOverdue only affects given school', function () {
    $schoolA = School::factory()->create(['is_active' => true]);
    $schoolB = School::factory()->create(['is_active' => true]);

    $makeIssue = function ($school) {
        $category = \App\Models\Facilities\BookCategory::create(['school_id' => $school->id, 'name' => 'Fiksi']);
        $book = Book::create([
            'school_id' => $school->id, 'book_category_id' => $category->id,
            'title' => 'Buku', 'author' => 'Anonim',
            'isbn' => 'ISBN-' . $school->id . rand(1000, 9999),
            'total_quantity' => 5, 'available_quantity' => 5,
        ]);
        $member = User::factory()->create(['school_id' => $school->id]);
        return BookIssue::create([
            'school_id' => $school->id, 'book_id' => $book->id,
            'issued_to' => $member->id, 'issued_by' => $member->id,
            'issue_date' => today()->subDays(30), 'due_date' => today()->subDays(5),
            'status' => 'issued',
        ]);
    };

    $issueA = $makeIssue($schoolA);
    $issueB = $makeIssue($schoolB);

    $count = app(LibraryService::class)->markOverdue($schoolA->id);

    expect($count)->toBe(1);
    expect($issueA->fresh()->status)->toBe('overdue');
    expect($issueB->fresh()->status)->toBe('issued');
});
