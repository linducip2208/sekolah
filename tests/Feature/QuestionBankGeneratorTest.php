<?php

use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Exam;
use App\Models\Academic\Medium;
use App\Models\Academic\Section;
use App\Models\Academic\Student;
use App\Models\Academic\Subject;
use App\Models\QuestionBank\QuestionBankCategory;
use App\Models\QuestionBank\QuestionBankItem;
use App\Models\School;
use App\Models\User;
use App\Services\Academic\ItemAnalysisService;
use App\Services\QuestionBank\QuestionBankService;
use Spatie\Permission\Models\Role;

function qbankBuildExam(): Exam
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

it('generates exam questions from question bank and maps type/answer correctly', function () {
    $exam = qbankBuildExam();

    $category = QuestionBankCategory::create([
        'school_id' => $exam->school_id, 'subject_id' => $exam->subject_id, 'name' => 'Bab 1',
    ]);

    QuestionBankItem::create([
        'school_id' => $exam->school_id,
        'subject_id' => $exam->subject_id,
        'question_bank_category_id' => $category->id,
        'author_id' => null,
        'question_html' => '2 + 2 = ?',
        'type' => 'multiple_choice',
        'options' => [['text' => '3', 'is_correct' => false], ['text' => '4', 'is_correct' => true]],
        'answer_key' => '4',
        'difficulty' => 'medium',
        'is_published' => true,
    ]);

    $service = new QuestionBankService();
    $items = $service->generateExamQuestions($exam->school_id, $exam->subject_id, ['medium' => 1]);
    $created = $service->attachToExam($exam, $items);

    expect($created)->toBe(1);

    $question = $exam->questions()->first();
    expect($question->type)->toBe('mcq');
    expect($question->correct_answer)->toBe('4');
    expect($question->question_bank_item_id)->toBe($items->first()->id);
});

it('maps short_answer and true_false bank types to exam types', function () {
    $service = new QuestionBankService();

    expect($service->toExamType('multiple_choice'))->toBe('mcq');
    expect($service->toExamType('true_false'))->toBe('true_false');
    expect($service->toExamType('short_answer'))->toBe('essay');
    expect($service->toExamType('essay'))->toBe('essay');

    expect($service->normalizeAnswerKey(['A', 'B']))->toBe('A,B');
    expect($service->normalizeAnswerKey('Benar'))->toBe('Benar');
    expect($service->normalizeAnswerKey(null))->toBeNull();
});

it('aggregates item analysis back to the source bank item', function () {
    $exam = qbankBuildExam();

    $category = QuestionBankCategory::create([
        'school_id' => $exam->school_id, 'subject_id' => $exam->subject_id, 'name' => 'Bab 1',
    ]);

    $item = QuestionBankItem::create([
        'school_id' => $exam->school_id,
        'subject_id' => $exam->subject_id,
        'question_bank_category_id' => $category->id,
        'question_html' => '1 + 1 = ?',
        'type' => 'multiple_choice',
        'options' => [['text' => '2', 'is_correct' => true], ['text' => '3', 'is_correct' => false]],
        'answer_key' => '2',
        'difficulty' => 'easy',
        'is_published' => true,
    ]);

    $question = $exam->questions()->create([
        'school_id' => $exam->school_id,
        'question_bank_item_id' => $item->id,
        'question' => '1 + 1 = ?',
        'type' => 'mcq',
        'options' => [['text' => '2'], ['text' => '3']],
        'correct_answer' => '2',
        'marks' => 10,
        'order' => 0,
    ]);

    $studentUser = User::factory()->create(['school_id' => $exam->school_id]);
    $student = Student::create([
        'user_id' => $studentUser->id, 'school_id' => $exam->school_id, 'admission_no' => 'NIS-' . uniqid(),
    ]);
    \App\Models\Academic\ExamResult::create([
        'school_id' => $exam->school_id, 'exam_id' => $exam->id, 'student_id' => $student->id,
        'obtained_marks' => 10, 'status' => 'passed', 'answers' => [$question->id => '2'],
    ]);

    (new ItemAnalysisService())->analyze($exam->fresh());

    $item->refresh();
    expect((float) $item->avg_score_pct)->toBe(100.0);
    expect((float) $item->discrimination)->toBe(0.0);
});
