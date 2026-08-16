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
use Spatie\Permission\Models\Role;

function featureBuildExam(): Exam
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

    return Exam::create([
        'school_id' => $school->id,
        'class_section_id' => $classSection->id,
        'subject_id' => $subject->id,
        'title' => 'UTS',
        'type' => 'online',
        'total_marks' => 10,
        'pass_marks' => 5,
    ]);
}

it('renders exam item analysis page for an admin', function () {
    $school = School::factory()->create();
    $admin  = User::factory()->create(['school_id' => $school->id]);
    Role::firstOrCreate(['name' => 'admin']);
    $admin->assignRole('admin');

    $exam = featureBuildExam();
    $exam->update(['school_id' => $school->id]);

    $q = ExamQuestion::create([
        'school_id' => $school->id, 'exam_id' => $exam->id,
        'question' => '1 + 1 = ?', 'type' => 'mcq',
        'options' => [['text' => '2'], ['text' => '3']],
        'correct_answer' => '2', 'marks' => 10, 'order' => 0,
    ]);

    $student = Student::create([
        'user_id' => $admin->id, 'school_id' => $school->id, 'admission_no' => 'NIS-1',
    ]);
    ExamResult::create([
        'school_id' => $school->id, 'exam_id' => $exam->id, 'student_id' => $student->id,
        'obtained_marks' => 10, 'status' => 'passed', 'answers' => [$q->id => '2'],
    ]);

    $this->actingAs($admin)
        ->get(route('admin.exams.analysis', $exam))
        ->assertOk()
        ->assertSee('Analisis Butir Soal')
        ->assertSee('Mudah');
});
