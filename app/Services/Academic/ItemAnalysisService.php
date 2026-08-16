<?php

namespace App\Services\Academic;

use App\Models\Academic\Exam;
use App\Models\Academic\ExamQuestion;
use App\Models\QuestionBank\QuestionBankItem;
use Illuminate\Support\Collection;

class ItemAnalysisService
{
    /**
     * Compute classical item analysis for every auto-graded question of an exam.
     *  - difficulty index  (p-value): proportion of students answering correctly
     *  - discrimination index (D):   top 27% vs bottom 27% correct-rate gap
     *  - distractor analysis:        answer choice frequencies (which is correct)
     *
     * Results are persisted on each question and returned as a rich array for the UI.
     */
    public function analyze(Exam $exam): array
    {
        $questions = $exam->questions()->orderBy('order')->get();
        $results   = $exam->results()->whereIn('status', ['passed', 'failed'])->get();

        $n         = $results->count();
        $groupSize = $n > 0 ? max(1, (int) round($n * 0.27)) : 0;

        $topIds    = $this->groupIds($results, $groupSize, 'top');
        $bottomIds = $this->groupIds($results, $groupSize, 'bottom');

        $rows = $questions->map(function (ExamQuestion $q) use ($results, $topIds, $bottomIds, $groupSize) {
            if (!in_array($q->type, ['mcq', 'true_false'], true)) {
                $q->update([
                    'difficulty_index'      => null,
                    'discrimination_index'  => null,
                    'distractor_analysis'   => null,
                ]);

                return [
                    'id'            => $q->id,
                    'question'      => $q->question,
                    'type'          => $q->type,
                    'marks'         => $q->marks,
                    'correct_answer'=> $q->correct_answer,
                    'answered'      => 0,
                    'correct'       => 0,
                    'difficulty'    => null,
                    'difficulty_label' => 'Tidak dinilai (esai)',
                    'difficulty_tone'  => 'muted',
                    'discrimination'=> null,
                    'discrimination_label' => '—',
                    'discrimination_tone'  => 'muted',
                    'distractors'   => [],
                ];
            }

            $correctKey = (string) $q->correct_answer;

            $distractors = [];
            $answered    = 0;
            $correct     = 0;
            $topCorrect  = 0;
            $bottomCorrect = 0;

            foreach ($results as $r) {
                $ans = $r->answers[$q->id] ?? null;
                if ($ans === null || $ans === '') {
                    continue;
                }

                $answered++;
                $key = (string) $ans;
                $distractors[$key] = ($distractors[$key] ?? 0) + 1;

                $isCorrect = $key === $correctKey;
                if ($isCorrect) {
                    $correct++;
                    if ($topIds->contains($r->id)) {
                        $topCorrect++;
                    }
                    if ($bottomIds->contains($r->id)) {
                        $bottomCorrect++;
                    }
                }
            }

            $difficulty = $answered > 0 ? round($correct / $answered, 3) : null;
            $discrimination = $groupSize > 0 ? round(($topCorrect - $bottomCorrect) / $groupSize, 3) : null;

            $distractorAnalysis = collect($distractors)
                ->map(fn ($count, $key) => [
                    'answer'     => $key,
                    'count'      => $count,
                    'percentage' => $answered > 0 ? round(($count / $answered) * 100, 1) : 0,
                    'is_correct' => $key === $correctKey,
                ])
                ->sortByDesc('count')
                ->values()
                ->all();

            $q->update([
                'difficulty_index'     => $difficulty,
                'discrimination_index' => $discrimination,
                'distractor_analysis'  => $distractorAnalysis,
            ]);

            if ($q->question_bank_item_id) {
                QuestionBankItem::where('id', $q->question_bank_item_id)->update([
                    'avg_score_pct'  => $difficulty !== null ? round($difficulty * 100, 2) : null,
                    'discrimination' => $discrimination,
                ]);
            }

            return [
                'id'            => $q->id,
                'question'      => $q->question,
                'type'          => $q->type,
                'marks'         => $q->marks,
                'correct_answer'=> $q->correct_answer,
                'answered'      => $answered,
                'correct'       => $correct,
                'difficulty'    => $difficulty,
                'difficulty_label' => $this->difficultyInterpretation($difficulty)['label'],
                'difficulty_tone'  => $this->difficultyInterpretation($difficulty)['tone'],
                'discrimination'=> $discrimination,
                'discrimination_label' => $this->discriminationInterpretation($discrimination)['label'],
                'discrimination_tone'  => $this->discriminationInterpretation($discrimination)['tone'],
                'distractors'   => $distractorAnalysis,
            ];
        });

        return [
            'total_students'   => $n,
            'total_questions'  => $questions->count(),
            'group_size'       => $groupSize,
            'questions'        => $rows,
            'summary'          => $this->summarize($rows),
        ];
    }

    private function groupIds(Collection $results, int $groupSize, string $which): Collection
    {
        $sorted = $results->sortByDesc('obtained_marks')->values();

        if ($groupSize === 0) {
            return collect();
        }

        $ids = $which === 'top'
            ? $sorted->take($groupSize)->pluck('id')
            : $sorted->take(-$groupSize)->pluck('id');

        return $ids;
    }

    private function summarize(Collection $rows): array
    {
        $scored = $rows->filter(fn ($r) => $r['difficulty'] !== null);

        return [
            'hard'     => $scored->filter(fn ($r) => $r['difficulty'] < 0.3)->count(),
            'medium'   => $scored->filter(fn ($r) => $r['difficulty'] >= 0.3 && $r['difficulty'] <= 0.7)->count(),
            'easy'     => $scored->filter(fn ($r) => $r['difficulty'] > 0.7)->count(),
            'good_discrimination' => $scored->filter(fn ($r) => $r['discrimination'] >= 0.3)->count(),
            'needs_revision' => $scored->filter(fn ($r) => $r['discrimination'] < 0.2)->count(),
        ];
    }

    public function difficultyInterpretation(?float $p): array
    {
        if ($p === null) {
            return ['label' => '—', 'tone' => 'muted'];
        }

        return match (true) {
            $p < 0.3  => ['label' => 'Sulit',  'tone' => 'danger'],
            $p <= 0.7 => ['label' => 'Sedang', 'tone' => 'warning'],
            default   => ['label' => 'Mudah',  'tone' => 'success'],
        };
    }

    public function discriminationInterpretation(?float $d): array
    {
        if ($d === null) {
            return ['label' => '—', 'tone' => 'muted'];
        }

        return match (true) {
            $d >= 0.4 => ['label' => 'Sangat Baik', 'tone' => 'success'],
            $d >= 0.3 => ['label' => 'Baik',         'tone' => 'success'],
            $d >= 0.2 => ['label' => 'Cukup',        'tone' => 'warning'],
            default   => ['label' => 'Buruk (revisi)','tone' => 'danger'],
        };
    }
}
