<?php

namespace App\Services;

use App\Models\Academic\AssignmentSubmission;

class AutoGradingService
{
    public function grade(AssignmentSubmission $submission): array
    {
        $assignment = $submission->assignment()->with('questions')->first();
        if (!$assignment || !$assignment->auto_grade) {
            return ['score' => 0, 'feedback' => 'Tidak ada auto-grading.'];
        }

        $questions = $assignment->questions;
        $answerKey = $assignment->answer_key;
        $studentAnswers = json_decode($submission->answer, true) ?? [];

        if (empty($questions) || empty($answerKey)) {
            return ['score' => 0, 'feedback' => 'Kunci jawaban tidak tersedia.'];
        }

        $totalPoints = 0;
        $earnedPoints = 0;
        $correctCount = 0;
        $incorrectCount = 0;

        foreach ($questions as $q) {
            $totalPoints += $q->points;
            $studentAnswer = $studentAnswers[$q->question_number] ?? null;
            $correctAnswer = $answerKey[$q->question_number] ?? null;

            if ($q->question_type === 'mcq') {
                if ($studentAnswer === null) {
                    continue; // no answer
                }
                if ($studentAnswer === $correctAnswer) {
                    $earnedPoints += $q->points;
                    $correctCount++;
                } else {
                    $incorrectCount++;
                }
            }
        }

        if ($totalPoints === 0) {
            return ['score' => 0, 'feedback' => 'N/A'];
        }

        $score = round(($earnedPoints / $totalPoints) * $assignment->total_marks, 2);
        $pct = round(($earnedPoints / $totalPoints) * 100, 1);

        $feedback = "Auto-graded: {$correctCount} benar, {$incorrectCount} salah. Skor: {$earnedPoints}/{$totalPoints} ({$pct}%).";

        return ['score' => $score, 'feedback' => $feedback];
    }

    public function gradeShortAnswer(string $studentAnswer, string $referenceAnswer): array
    {
        $similarity = $this->calculateSimilarity(strtolower(trim($studentAnswer)), strtolower(trim($referenceAnswer)));

        if ($similarity >= 0.90) {
            $feedback = 'Jawaban sangat sesuai dengan referensi.';
        } elseif ($similarity >= 0.65) {
            $feedback = 'Jawaban cukup sesuai, beberapa poin mendekati referensi.';
        } elseif ($similarity >= 0.40) {
            $feedback = 'Jawaban kurang sesuai, perlu pengembangan lebih lanjut.';
        } else {
            $feedback = 'Jawaban tidak sesuai dengan referensi.';
        }

        return ['similarity' => round($similarity * 100, 1), 'feedback' => $feedback];
    }

    private function calculateSimilarity(string $text1, string $text2): float
    {
        $words1 = array_unique(array_filter(explode(' ', $text1)));
        $words2 = array_unique(array_filter(explode(' ', $text2)));

        if (empty($words1) || empty($words2)) {
            return 0.0;
        }

        $intersection = array_intersect($words1, $words2);
        $union = array_unique(array_merge($words1, $words2));

        return count($intersection) / count($union);
    }
}
