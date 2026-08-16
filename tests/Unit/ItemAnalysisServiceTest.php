<?php

use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Exam;
use App\Models\Academic\ExamQuestion;
use App\Models\Academic\ExamResult;
use App\Models\Academic\Medium;
use App\Models\Academic\Section;
use App\Models\Academic\Student;
use App\Models\Academic\Subject;
use App\Models\School;
use App\Models\User;
use App\Services\Academic\ItemAnalysisService;

function buildExam(): Exam
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
        'total_marks' => 20,
        'pass_marks' => 10,
    ]);
}

function addStudent(Exam $exam, array $answers, int $score): void
{
    $user = User::factory()->create(['school_id' => $exam->school_id]);
    $student = Student::create([
        'user_id' => $user->id,
        'school_id' => $exam->school_id,
        'admission_no' => 'NIS-' . uniqid(),
    ]);

    ExamResult::create([
        'school_id' => $exam->school_id,
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'obtained_marks' => $score,
        'status' => $score >= $exam->pass_marks ? 'passed' : 'failed',
        'answers' => $answers,
    ]);
}

beforeEach(fn () => $this->service = new ItemAnalysisService());

it('computes difficulty index, discrimination index and distractor analysis', function () {
    $exam = buildExam();

    $q1 = ExamQuestion::create([
        'school_id' => $exam->school_id, 'exam_id' => $exam->id,
        'question' => '2 + 2 = ?', 'type' => 'mcq',
        'options' => [['text' => '3'], ['text' => '4']],
        'correct_answer' => '4', 'marks' => 10, 'order' => 0,
    ]);
    $q2 = ExamQuestion::create([
        'school_id' => $exam->school_id, 'exam_id' => $exam->id,
        'question' => 'Ibu kota Indonesia?', 'type' => 'mcq',
        'options' => [['text' => 'Jakarta'], ['text' => 'Bandung']],
        'correct_answer' => 'Jakarta', 'marks' => 10, 'order' => 1,
    ]);

    addStudent($exam, [$q1->id => '4', $q2->id => 'Jakarta'], 20);
    addStudent($exam, [$q1->id => '4', $q2->id => 'Bandung'], 10);
    addStudent($exam, [$q1->id => '3', $q2->id => 'Jakarta'], 10);
    addStudent($exam, [$q1->id => '3', $q2->id => 'Bandung'], 0);

    $analysis = $this->service->analyze($exam->fresh());

    expect($analysis['total_students'])->toBe(4);
    expect($analysis['total_questions'])->toBe(2);

    $q1Row = $analysis['questions'][0];
    expect($q1Row['difficulty'])->toBe(0.5);
    expect($q1Row['difficulty_label'])->toBe('Sedang');
    expect($q1Row['discrimination'])->toBe(1.0);
    expect($q1Row['discrimination_label'])->toBe('Sangat Baik');
    expect(collect($q1Row['distractors'])->pluck('count', 'answer')->all())->toBe(['4' => 2, '3' => 2]);

    $q2Row = $analysis['questions'][1];
    expect($q2Row['correct'])->toBe(2);
    expect($q2Row['answered'])->toBe(4);

    $this->assertDatabaseHas('exam_questions', ['id' => $q1->id, 'difficulty_index' => 0.5, 'discrimination_index' => 1.0]);
});

it('skips essay questions in item analysis', function () {
    $exam = buildExam();

    ExamQuestion::create([
        'school_id' => $exam->school_id, 'exam_id' => $exam->id,
        'question' => 'Jelaskan fotosintesis', 'type' => 'essay',
        'correct_answer' => null, 'marks' => 10, 'order' => 0,
    ]);

    addStudent($exam, [], 0);

    $analysis = $this->service->analyze($exam->fresh());

    expect($analysis['questions'][0]['difficulty'])->toBeNull();
    expect($analysis['questions'][0]['difficulty_label'])->toBe('Tidak dinilai (esai)');
});
