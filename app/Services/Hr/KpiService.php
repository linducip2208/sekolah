<?php

namespace App\Services\Hr;

use App\Models\Hr\KpiAppraisal;
use App\Models\Hr\KpiCriteria;
use App\Models\Hr\KpiGoal;
use App\Models\Hr\KpiScore;
use App\Models\Hr\KpiTemplate;

class KpiService
{
    public function createAppraisal(int $schoolId, array $data): KpiAppraisal
    {
        $template = KpiTemplate::where('school_id', $schoolId)->findOrFail($data['template_id']);

        return KpiAppraisal::create([
            'school_id'   => $schoolId,
            'staff_id'    => $data['staff_id'],
            'template_id' => $template->id,
            'reviewer_id' => $data['reviewer_id'],
            'period'      => $data['period'],
            'status'      => 'draft',
        ]);
    }

    public function saveScores(KpiAppraisal $appraisal, array $scores): KpiAppraisal
    {
        abort_if($appraisal->status === 'finalized', 422, 'Penilaian sudah final.');

        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($scores as $scoreData) {
            $criteria = KpiCriteria::findOrFail($scoreData['criteria_id']);

            KpiScore::updateOrCreate(
                ['appraisal_id' => $appraisal->id, 'criteria_id' => $criteria->id],
                [
                    'school_id' => $appraisal->school_id,
                    'score'     => min($scoreData['score'], $criteria->max_score),
                    'evidence'  => $scoreData['evidence'] ?? null,
                ]
            );

            $totalWeight += $criteria->weight;
            $weightedSum += $scoreData['score'] * $criteria->weight;
        }

        $totalScore = $totalWeight > 0 ? (int) round($weightedSum / $totalWeight * 10) : 0;

        $appraisal->update(['total_score' => $totalScore]);

        return $appraisal->fresh();
    }

    public function submitAppraisal(KpiAppraisal $appraisal): KpiAppraisal
    {
        abort_if($appraisal->status !== 'draft', 422, 'Hanya appraisal draft yang bisa disubmit.');

        $appraisal->update(['status' => 'submitted']);
        return $appraisal->fresh();
    }

    public function finalizeAppraisal(KpiAppraisal $appraisal, ?string $notes = null): KpiAppraisal
    {
        abort_if($appraisal->status !== 'submitted', 422, 'Hanya appraisal submitted yang bisa difinalisasi.');

        $appraisal->update(['status' => 'finalized', 'reviewer_notes' => $notes]);
        return $appraisal->fresh();
    }

    public function createGoal(int $schoolId, array $data): KpiGoal
    {
        return KpiGoal::create(array_merge($data, ['school_id' => $schoolId]));
    }

    public function updateGoal(KpiGoal $goal, array $data): KpiGoal
    {
        $goal->update($data);
        return $goal->fresh();
    }

    /** Staff performance summary for a period */
    public function staffSummary(int $schoolId, int $staffId): array
    {
        $appraisals = KpiAppraisal::where('school_id', $schoolId)
            ->where('staff_id', $staffId)
            ->where('status', 'finalized')
            ->orderByDesc('period')
            ->get();

        $avgScore = $appraisals->avg('total_score');
        $goals = KpiGoal::where('school_id', $schoolId)
            ->where('staff_id', $staffId)
            ->get();

        $achieved = $goals->where('status', 'achieved')->count();
        $totalGoals = $goals->count();

        return [
            'appraisals'       => $appraisals,
            'average_score'    => $avgScore ? round($avgScore) : null,
            'latest_grade'     => $appraisals->first()?->grade,
            'goals_achieved'   => $achieved,
            'goals_total'      => $totalGoals,
            'goal_completion'  => $totalGoals > 0 ? round($achieved / $totalGoals * 100) : 0,
        ];
    }
}
