<?php

namespace App\Services\Discipline;

use App\Models\Discipline\DisciplineCategory;
use App\Models\Discipline\DisciplineRecord;
use Illuminate\Support\Facades\DB;

class DisciplineService
{
    public function record(int $schoolId, int $studentId, int $categoryId, int $reporterId, array $data): DisciplineRecord
    {
        return DB::transaction(function () use ($schoolId, $studentId, $categoryId, $reporterId, $data) {
            $category = DisciplineCategory::where('school_id', $schoolId)->findOrFail($categoryId);

            $record = DisciplineRecord::create([
                'school_id'              => $schoolId,
                'student_id'             => $studentId,
                'discipline_category_id' => $categoryId,
                'reported_by'            => $reporterId,
                'incident_date'          => $data['incident_date'] ?? today(),
                'description'            => $data['description'],
                'evidence_files'         => $data['evidence_files'] ?? null,
                'points'                 => $category->point_value,
                'status'                 => 'reported',
            ]);

            if ($category->auto_sanction) {
                $totalPoints = $this->totalPointsFor($schoolId, $studentId);
                $thresholds  = (array) ($category->sanction_thresholds ?? []);

                foreach ($thresholds as $rule) {
                    if (isset($rule['at_points']) && $totalPoints <= (int) $rule['at_points']) {
                        $record->update([
                            'status'           => 'sanctioned',
                            'sanction_applied' => $rule['action'] ?? 'review',
                            'parent_notified'  => true,
                        ]);
                        break;
                    }
                }
            }

            \App\Jobs\NotifyParentDisciplineJob::dispatch($record->id);

            return $record->fresh();
        });
    }

    public function totalPointsFor(int $schoolId, int $studentId, ?\DateTimeInterface $since = null): int
    {
        return (int) DisciplineRecord::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->when($since, fn ($q) => $q->where('incident_date', '>=', $since))
            ->sum('points');
    }

    public function leaderboardByPoints(int $schoolId, int $limit = 20)
    {
        return DisciplineRecord::where('school_id', $schoolId)
            ->selectRaw('student_id, SUM(points) as total_points')
            ->groupBy('student_id')
            ->orderByDesc('total_points')
            ->limit($limit)
            ->get();
    }
}
