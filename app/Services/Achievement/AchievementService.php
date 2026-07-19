<?php

namespace App\Services\Achievement;

use App\Models\Achievement\DigitalBadge;
use App\Models\Achievement\StudentAchievement;
use App\Models\Achievement\StudentBadge;

class AchievementService
{
    public function recordAchievement(int $schoolId, int $studentId, array $data): StudentAchievement
    {
        return StudentAchievement::create(array_merge($data, [
            'school_id'  => $schoolId,
            'student_id' => $studentId,
            'verified'   => false,
        ]));
    }

    public function verify(StudentAchievement $a, int $verifierId): StudentAchievement
    {
        $a->update(['verified' => true, 'verified_by' => $verifierId]);
        return $a->fresh();
    }

    public function evaluateBadges(int $schoolId, int $studentId, array $studentMetrics): int
    {
        $badges = DigitalBadge::where('school_id', $schoolId)->get();
        $awarded = 0;

        foreach ($badges as $badge) {
            $criteria = (array) $badge->award_criteria;
            $meets    = true;
            foreach ($criteria as $key => $threshold) {
                if (!isset($studentMetrics[$key]) || $studentMetrics[$key] < $threshold) {
                    $meets = false;
                    break;
                }
            }

            if (!$meets) continue;

            $exists = StudentBadge::where('student_id', $studentId)
                ->where('digital_badge_id', $badge->id)
                ->exists();

            if (!$exists) {
                StudentBadge::create([
                    'school_id'        => $schoolId,
                    'student_id'       => $studentId,
                    'digital_badge_id' => $badge->id,
                    'awarded_at'       => today(),
                ]);
                $awarded++;
            }
        }

        return $awarded;
    }

    public function studentLeaderboard(int $schoolId, int $limit = 20)
    {
        return StudentAchievement::where('school_id', $schoolId)
            ->where('verified', true)
            ->join('achievement_categories as ac', 'ac.id', '=', 'student_achievements.achievement_category_id')
            ->selectRaw('student_achievements.student_id, SUM(ac.points) as total_points')
            ->groupBy('student_achievements.student_id')
            ->orderByDesc('total_points')
            ->limit($limit)
            ->get();
    }
}
