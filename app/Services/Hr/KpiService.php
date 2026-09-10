<?php

namespace App\Services\Hr;

use App\Models\Academic\Staff;
use App\Models\Hr\KpiAppraisal;
use App\Models\Hr\KpiCriteria;
use App\Models\Hr\KpiGoal;
use App\Models\Hr\KpiScore;
use App\Models\Hr\KpiTemplate;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class KpiService
{
    public function createAppraisal(int $schoolId, array $data): KpiAppraisal
    {
        $this->assertSchoolAccess($schoolId);
        $template = KpiTemplate::where('school_id', $schoolId)->findOrFail($data['template_id']);
        Staff::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($data['staff_id']);
        User::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($data['reviewer_id']);

        return KpiAppraisal::create([
            'school_id' => $schoolId,
            'staff_id' => $data['staff_id'],
            'template_id' => $template->id,
            'reviewer_id' => $data['reviewer_id'],
            'period' => $data['period'],
            'status' => 'draft',
        ]);
    }

    public function saveScores(KpiAppraisal $appraisal, array $scores): KpiAppraisal
    {
        $schoolId = (int) $appraisal->school_id;
        $this->assertSchoolAccess($schoolId);

        return DB::transaction(function () use ($appraisal, $scores, $schoolId): KpiAppraisal {
            $appraisal = KpiAppraisal::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->lockForUpdate()
                ->findOrFail($appraisal->id);
            abort_if($appraisal->status === 'finalized', 422, 'Penilaian sudah final.');

            $totalWeight = 0;
            $weightedSum = 0;

            foreach ($scores as $scoreData) {
                $criteria = KpiCriteria::withoutGlobalScopes()
                    ->where('school_id', $schoolId)
                    ->where('template_id', $appraisal->template_id)
                    ->findOrFail($scoreData['criteria_id']);
                $score = max(0, min((int) $scoreData['score'], (int) $criteria->max_score));

                KpiScore::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'appraisal_id' => $appraisal->id,
                        'criteria_id' => $criteria->id,
                    ],
                    [
                        'score' => $score,
                        'evidence' => $scoreData['evidence'] ?? null,
                    ]
                );

                $totalWeight += $criteria->weight;
                $weightedSum += $score * $criteria->weight;
            }

            $totalScore = $totalWeight > 0 ? (int) round($weightedSum / $totalWeight * 10) : 0;
            $appraisal->update(['total_score' => $totalScore]);

            return $appraisal->fresh();
        });
    }

    public function submitAppraisal(KpiAppraisal $appraisal): KpiAppraisal
    {
        $appraisal = $this->scopedAppraisal($appraisal);
        abort_if($appraisal->status !== 'draft', 422, 'Hanya appraisal draft yang bisa disubmit.');

        $appraisal->update(['status' => 'submitted']);

        return $appraisal->fresh();
    }

    public function finalizeAppraisal(KpiAppraisal $appraisal, ?string $notes = null): KpiAppraisal
    {
        $appraisal = $this->scopedAppraisal($appraisal);
        abort_if($appraisal->status !== 'submitted', 422, 'Hanya appraisal submitted yang bisa difinalisasi.');

        $appraisal->update(['status' => 'finalized', 'reviewer_notes' => $notes]);

        return $appraisal->fresh();
    }

    public function createGoal(int $schoolId, array $data): KpiGoal
    {
        $this->assertSchoolAccess($schoolId);
        Staff::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($data['staff_id']);

        return KpiGoal::create(array_merge($data, ['school_id' => $schoolId]));
    }

    public function updateGoal(KpiGoal $goal, array $data): KpiGoal
    {
        $this->assertSchoolAccess((int) $goal->school_id);
        $goal = KpiGoal::withoutGlobalScopes()
            ->where('school_id', $goal->school_id)
            ->findOrFail($goal->id);
        $goal->update($data);

        return $goal->fresh();
    }

    /** Staff performance summary for a period */
    public function staffSummary(int $schoolId, int $staffId): array
    {
        $this->assertSchoolAccess($schoolId);
        Staff::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($staffId);

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
            'appraisals' => $appraisals,
            'average_score' => $avgScore ? round($avgScore) : null,
            'latest_grade' => $appraisals->first()?->grade,
            'goals_achieved' => $achieved,
            'goals_total' => $totalGoals,
            'goal_completion' => $totalGoals > 0 ? round($achieved / $totalGoals * 100) : 0,
        ];
    }

    private function scopedAppraisal(KpiAppraisal $appraisal): KpiAppraisal
    {
        $this->assertSchoolAccess((int) $appraisal->school_id);

        return KpiAppraisal::withoutGlobalScopes()
            ->where('school_id', $appraisal->school_id)
            ->findOrFail($appraisal->id);
    }

    private function assertSchoolAccess(int $schoolId): void
    {
        if (auth()->check() && (int) auth()->user()->school_id !== $schoolId && ! auth()->user()->hasRole('super_admin')) {
            abort(403, 'Akses sekolah tidak valid.');
        }
    }
}
