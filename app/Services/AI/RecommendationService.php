<?php

namespace App\Services\AI;

use App\Models\AI\AiRecommendation;
use App\Models\Analytics\StudentRiskScore;
use Illuminate\Support\Collection;

class RecommendationService
{
    /** Rule-based action map per risk level. */
    public const ACTIONS = [
        'critical' => [
            'Hubungi orang tua/wali segera',
            'Jadwalkan sesi konseling mendesak',
            'Pantau kehadiran harian',
            'Intervensi akademik oleh guru kelas',
            'Eskalasi ke kepala sekolah',
        ],
        'high' => [
            'Hubungi orang tua/wali',
            'Jadwalkan sesi konseling',
            'Pantau kehadiran',
            'Intervensi akademik',
        ],
        'medium' => [
            'Pantau kehadiran rutin',
            'Diskusi dengan guru kelas',
        ],
        'low' => [
            'Pemantauan rutin',
        ],
    ];

    /** Generate recommendations for at-risk students (dedupe). Returns count created. */
    public function generateFromRisk(int $schoolId): int
    {
        $latest = StudentRiskScore::where('school_id', $schoolId)
            ->orderByDesc('snapshot_date')
            ->get()
            ->unique('student_id');

        $count = 0;

        foreach ($latest as $risk) {
            $level = $risk->risk_level ?? 'medium';
            if (!in_array($level, ['high', 'critical'], true)) {
                continue;
            }

            $exists = AiRecommendation::where('school_id', $schoolId)
                ->where('student_id', $risk->student_id)
                ->where('type', 'student_risk')
                ->where('status', 'pending')
                ->exists();

            if ($exists) {
                continue;
            }

            AiRecommendation::create([
                'school_id'  => $schoolId,
                'student_id' => $risk->student_id,
                'type'       => 'student_risk',
                'risk_level' => $level,
                'actions'    => self::ACTIONS[$level] ?? self::ACTIONS['medium'],
                'status'     => 'pending',
            ]);

            $count++;
        }

        return $count;
    }

    public function pending(int $schoolId): Collection
    {
        return AiRecommendation::where('school_id', $schoolId)
            ->where('status', 'pending')
            ->with('student.user')
            ->orderByDesc('risk_level')
            ->orderByDesc('created_at')
            ->get();
    }

    public function action(AiRecommendation $recommendation, int $userId, ?string $note = null): AiRecommendation
    {
        $recommendation->update([
            'status'      => 'actioned',
            'reviewed_by' => $userId,
            'reviewed_at' => now(),
            'note'        => $note,
        ]);

        return $recommendation->fresh();
    }

    public function dismiss(AiRecommendation $recommendation, int $userId, ?string $note = null): AiRecommendation
    {
        $recommendation->update([
            'status'      => 'dismissed',
            'reviewed_by' => $userId,
            'reviewed_at' => now(),
            'note'        => $note,
        ]);

        return $recommendation->fresh();
    }
}
