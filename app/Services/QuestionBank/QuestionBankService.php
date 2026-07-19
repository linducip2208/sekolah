<?php

namespace App\Services\QuestionBank;

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

    public function recordItemAnalytics(int $itemId, float $avgScorePct, ?float $discrimination = null): void
    {
        QuestionBankItem::where('id', $itemId)->update(array_filter([
            'avg_score_pct'  => $avgScorePct,
            'discrimination' => $discrimination,
        ], fn ($v) => $v !== null));
    }
}
