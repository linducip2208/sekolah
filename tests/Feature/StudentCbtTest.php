<?php

use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Exam;
use App\Models\Academic\ExamQuestion;
use App\Models\Academic\Medium;
use App\Models\Academic\Section;
use App\Models\Academic\Student;
use App\Models\Academic\Subject;
use App\Models\School;
use App\Models\User;
use Spatie\Permission\Models\Role;

function cbtBuildExam(): array
{
    $school = School::factory()->create();
    $medium = Medium::create(['school_id' => $school->id, 'name' => 'Umum']);
    $room   = ClassRoom::create(['school_id' => $school->id, 'medium_id' => $medium->id, 'name' => '10']);
    $section = Section::create(['school_id' => $school->id, 'name' => 'A']);
    $year = AcademicYear::create([
        'school_id' => $school->id, 'name' => '2025/2026',
        'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true,
    ]);
    $classSection = ClassSection::create([
        'school_id' => $school->id, 'class_room_id' => $room->id,
        'section_id' => $section->id, 'medium_id' => $medium->id,
        'academic_year_id' => $year->id,
    ]);
    $subject = Subject::create(['school_id' => $school->id, 'medium_id' => $medium->id, 'name' => 'Matematika']);

    $exam = Exam::create([
        'school_id' => $school->id, 'class_section_id' => $classSection->id,
        'subject_id' => $subject->id, 'title' => 'UTS CBT', 'type' => 'online',
        'total_marks' => 10, 'pass_marks' => 5, 'duration_minutes' => 60,
    ]);

    ExamQuestion::create([
        'school_id' => $school->id, 'exam_id' => $exam->id,
        'question' => '2 + 2 = ?', 'type' => 'mcq',
        'options' => [['text' => '3'], ['text' => '4']],
        'correct_answer' => '4', 'marks' => 10, 'order' => 0,
    ]);

    $studentUser = User::factory()->create(['school_id' => $school->id]);
    Role::firstOrCreate(['name' => 'student']);
    $studentUser->assignRole('student');
    $student = Student::create([
        'user_id' => $studentUser->id, 'school_id' => $school->id,
        'class_section_id' => $classSection->id, 'admission_no' => 'CBT-1',
    ]);

    return [$studentUser, $student, $exam];
}

it('lists online exams for the student', function () {
    [$user, , ] = cbtBuildExam();

    $this->actingAs($user)->get(route('student.exams.index'))->assertOk()->assertSee('UTS CBT');
});

it('lets a student take an exam and submit answers', function () {
    [$user, , $exam] = cbtBuildExam();
    $question = $exam->questions()->first();

    $this->actingAs($user)
        ->get(route('student.exams.take', $exam))
        ->assertOk()
        ->assertSee('2 + 2 = ?');

    $this->actingAs($user)
        ->post(route('student.exams.submit', $exam), ['answers' => [$question->id => '4']])
        ->assertRedirect(route('student.exams.result', $exam));

    $this->assertDatabaseHas('exam_results', [
        'exam_id' => $exam->id,
        'status' => 'passed',
        'obtained_marks' => 10,
    ]);
});

it('shows exam result with review', function () {
    [$user, , $exam] = cbtBuildExam();
    $question = $exam->questions()->first();

    $this->actingAs($user)->get(route('student.exams.take', $exam));
    $this->actingAs($user)->post(route('student.exams.submit', $exam), ['answers' => [$question->id => '3']]);

    $this->actingAs($user)
        ->get(route('student.exams.result', $exam))
        ->assertOk()
        ->assertSee('TIDAK LULUS');
});
