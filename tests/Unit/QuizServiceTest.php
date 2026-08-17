<?php

use App\Models\Academic\Student;
use App\Models\Lms\Quiz;
use App\Models\Lms\QuizQuestion;
use App\Models\QuestionBank\QuestionBankCategory;
use App\Models\QuestionBank\QuestionBankItem;
use App\Models\School;
use App\Models\User;
use App\Services\Lms\QuizService;

beforeEach(function () {
    $this->service = app(QuizService::class);
    $this->school = School::factory()->create();
    $user = User::factory()->create(['school_id' => $this->school->id]);
    $this->student = Student::create([
        'user_id' => $user->id, 'school_id' => $this->school->id, 'admission_no' => 'Q-1',
    ]);
    $this->quiz = Quiz::create([
        'school_id' => $this->school->id, 'title' => 'Kuis Matematika', 'pass_score' => 50, 'is_published' => true,
    ]);
});

it('auto-grades a quiz submission with instant feedback', function () {
    $q1 = QuizQuestion::create([
        'school_id' => $this->school->id, 'quiz_id' => $this->quiz->id,
        'question' => '2 + 2 = ?', 'type' => 'mcq',
        'options' => [['text' => '3'], ['text' => '4']], 'correct_answer' => '4', 'order' => 1,
    ]);
    QuizQuestion::create([
        'school_id' => $this->school->id, 'quiz_id' => $this->quiz->id,
        'question' => 'Bumi bulat?', 'type' => 'true_false', 'correct_answer' => 'true', 'order' => 2,
    ]);

    $result = $this->service->submit($this->quiz, $this->student->id, [
        $q1->id => '4',
        // second question not answered
    ]);

    expect($result['score'])->toBe(1);
    expect($result['total'])->toBe(2);
    expect($result['percent'])->toBe(50);
    expect($result['passed'])->toBeTrue();
    expect($result['feedback'][0]['is_correct'])->toBeTrue();
    expect($result['feedback'][1]['is_correct'])->toBeFalse();
});

it('generates quiz questions from the question bank', function () {
    $medium = \App\Models\Academic\Medium::create(['school_id' => $this->school->id, 'name' => 'Umum']);
    $subject = \App\Models\Academic\Subject::create([
        'school_id' => $this->school->id, 'medium_id' => $medium->id, 'name' => 'Matematika',
    ]);

    $category = QuestionBankCategory::create([
        'school_id' => $this->school->id, 'subject_id' => $subject->id, 'name' => 'Bab 1',
    ]);
    QuestionBankItem::create([
        'school_id' => $this->school->id, 'subject_id' => $subject->id,
        'question_bank_category_id' => $category->id,
        'question_html' => '5 + 5 = ?', 'type' => 'multiple_choice',
        'options' => [['text' => '10', 'is_correct' => true], ['text' => '9', 'is_correct' => false]],
        'answer_key' => '10', 'difficulty' => 'easy', 'is_published' => true,
    ]);

    $created = $this->service->generateFromBank($this->quiz, 1);

    expect($created)->toBe(1);
    expect($this->quiz->questions()->count())->toBe(1);
    expect($this->quiz->questions()->first()->correct_answer)->toBe('10');
});
