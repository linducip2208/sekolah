<?php

namespace App\Services\QuestionBank;

use App\Models\Academic\Exam;
use App\Models\Academic\ExamQuestion;
use App\Models\QuestionBank\QuestionBankItem;
use Illuminate\Support\Collection;

class QuestionBankService
{
    /**
     * Generate randomized exam from bank with target distribution.
     * $distribution: ['easy' => 5, 'medium' => 3, 'hard' => 2]
     */
    public function generateExamQuestions(int $schoolId, int $subjectId, array $distribution, ?int $categoryId = null): Collection
    {
        $picked = collect();

        foreach ($distribution as $difficulty => $count) {
            $items = QuestionBankItem::where('school_id', $schoolId)
                ->where('subject_id', $subjectId)
                ->where('is_published', true)
                ->where('difficulty', $difficulty)
                ->when($categoryId, fn ($q) => $q->where('question_bank_category_id', $categoryId))
                ->inRandomOrder()
                ->limit((int) $count)
                ->get();

            $picked = $picked->concat($items);
        }

        QuestionBankItem::whereIn('id', $picked->pluck('id'))->increment('used_count');

        return $picked;
    }

    /**
     * Attach bank items to an exam as ExamQuestion rows (linked to their source).
     * Returns the number of questions created.
     */
    public function attachToExam(Exam $exam, Collection $items): int
    {
        $order = $exam->questions()->max('order') ?? 0;
        $created = 0;

        foreach ($items as $item) {
            $exam->questions()->create([
                'school_id'             => $exam->school_id,
                'question_bank_item_id' => $item->id,
                'question'              => $item->question_html,
                'type'                  => $this->toExamType($item->type),
                'options'               => $item->options,
                'correct_answer'        => $this->normalizeAnswerKey($item->answer_key),
                'marks'                 => 1,
                'order'                 => ++$order,
            ]);
            $created++;
        }

        return $created;
    }

    public function toExamType(string $bankType): string
    {
        return match ($bankType) {
            'multiple_choice' => 'mcq',
            'true_false'      => 'true_false',
            default           => 'essay',
        };
    }

    public function normalizeAnswerKey(mixed $key): ?string
    {
        if ($key === null) {
            return null;
        }

        if (is_array($key)) {
            return implode(',', $key);
        }

        return (string) $key;
    }

    public function recordItemAnalytics(int $itemId, float $avgScorePct, ?float $discrimination = null): void
    {
        QuestionBankItem::where('id', $itemId)->update(array_filter([
            'avg_score_pct'  => $avgScorePct,
            'discrimination' => $discrimination,
        ], fn ($v) => $v !== null));
    }
}
