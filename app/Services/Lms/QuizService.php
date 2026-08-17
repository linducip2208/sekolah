<?php

namespace App\Services\Lms;

use App\Models\Lms\Quiz;
use App\Models\Lms\QuizAttempt;
use App\Models\Lms\QuizQuestion;
use App\Models\QuestionBank\QuestionBankItem;

class QuizService
{
    /** Generate quiz questions from published question bank items. */
    public function generateFromBank(Quiz $quiz, int $count): int
    {
        $items = QuestionBankItem::where('school_id', $quiz->school_id)
            ->where('is_published', true)
            ->whereIn('type', ['multiple_choice', 'true_false'])
            ->inRandomOrder()
            ->limit($count)
            ->get();

        $order = $quiz->questions()->max('order') ?? 0;
        $created = 0;

        foreach ($items as $item) {
            $quiz->questions()->create([
                'school_id'            => $quiz->school_id,
                'question_bank_item_id'=> $item->id,
                'question'             => $item->question_html,
                'type'                 => $item->type === 'true_false' ? 'true_false' : 'mcq',
                'options'              => $item->options,
                'correct_answer'       => is_array($item->answer_key) ? implode(',', $item->answer_key) : (string) $item->answer_key,
                'order'                => ++$order,
            ]);
            $created++;
        }

        return $created;
    }

    /** Submit answers, auto-grade, and return per-question feedback. */
    public function submit(Quiz $quiz, int $studentId, array $answers): array
    {
        $questions = $quiz->questions()->get();

        $score   = 0;
        $total   = $questions->count();
        $feedback = [];

        foreach ($questions as $q) {
            $given = $answers[$q->id] ?? null;
            $isCorrect = $given !== null && (string) $given === (string) $q->correct_answer;

            if ($isCorrect) {
                $score++;
            }

            $feedback[] = [
                'question_id'   => $q->id,
                'question'      => $q->question,
                'correct_answer'=> $q->correct_answer,
                'given_answer'  => $given,
                'is_correct'    => $isCorrect,
            ];
        }

        $attemptNo = $quiz->attempts()->where('student_id', $studentId)->count() + 1;
        $pct = $total > 0 ? (int) round($score / $total * 100) : 0;

        QuizAttempt::create([
            'school_id'    => $quiz->school_id,
            'quiz_id'      => $quiz->id,
            'student_id'   => $studentId,
            'attempt_no'   => $attemptNo,
            'score'        => $score,
            'total'        => $total,
            'passed'       => $pct >= $quiz->pass_score,
            'answers'      => $answers,
            'submitted_at' => now(),
        ]);

        return [
            'score'    => $score,
            'total'    => $total,
            'percent'  => $pct,
            'passed'   => $pct >= $quiz->pass_score,
            'attempt_no' => $attemptNo,
            'feedback' => $feedback,
        ];
    }
}
