<?php

namespace App\Services\Analytics;

use App\Models\Academic\Attendance;
use App\Models\Academic\Student;
use App\Models\Analytics\StudentRiskScore;
use App\Models\Discipline\DisciplineRecord;

class RiskScoreService
{
    public function computeForSchool(int $schoolId): int
    {
        $count = 0;
        Student::where('school_id', $schoolId)->chunk(200, function ($students) use ($schoolId, &$count) {
            foreach ($students as $student) {
                $this->computeForStudent($schoolId, $student->id);
                $count++;
            }
        });
        return $count;
    }

    public function computeForStudent(int $schoolId, int $studentId): StudentRiskScore
    {
        $sinceDate = now()->subDays(30);

        // Attendance score (0-100, higher better)
        $totalAttendance   = Attendance::where('school_id', $schoolId)
            ->where('student_id', $studentId)->where('date', '>=', $sinceDate)->count();
        $presentCount = Attendance::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->where('date', '>=', $sinceDate)
            ->whereIn('status', ['present', 'late'])
            ->count();
        $attendanceScore = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 2) : 100;

        // Academic score: weighted recent marks (placeholder average)
        $academicAvg = (float) \DB::table('marks')
            ->where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->where('created_at', '>=', $sinceDate)
            ->selectRaw('AVG((obtained_marks / NULLIF(total_marks, 0)) * 100) as avg_pct')
            ->value('avg_pct') ?? 75;

        // Behavior score: 100 - (negative discipline points * factor)
        $negativePoints = abs((int) DisciplineRecord::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->where('incident_date', '>=', $sinceDate)
            ->where('points', '<', 0)
            ->sum('points'));
        $behaviorScore = max(0, 100 - $negativePoints * 5);

        // Engagement: avg of attendance + academic
        $engagementScore = round(($attendanceScore + $academicAvg) / 2, 2);

        // Overall risk (0=low risk, 100=high risk)
        $overallRisk = round(100 - (($attendanceScore + $academicAvg + $behaviorScore + $engagementScore) / 4), 2);

        $level = match (true) {
            $overallRisk >= 70 => 'critical',
            $overallRisk >= 50 => 'high',
            $overallRisk >= 30 => 'medium',
            default            => 'low',
        };

        $factors = [];
        if ($attendanceScore < 80) $factors[] = 'low_attendance';
        if ($academicAvg < 60)     $factors[] = 'low_academic';
        if ($negativePoints > 10)  $factors[] = 'discipline_issues';

        return StudentRiskScore::updateOrCreate(
            ['student_id' => $studentId, 'snapshot_date' => today()],
            [
                'school_id'         => $schoolId,
                'attendance_score'  => $attendanceScore,
                'academic_score'    => round($academicAvg, 2),
                'behavior_score'    => $behaviorScore,
                'engagement_score'  => $engagementScore,
                'overall_risk'      => $overallRisk,
                'risk_level'        => $level,
                'top_risk_factors'  => $factors,
                'recommendations'   => $this->buildRecommendations($factors),
            ],
        );
    }

    public function topAtRisk(int $schoolId, int $limit = 20)
    {
        return StudentRiskScore::where('school_id', $schoolId)
            ->whereDate('snapshot_date', today())
            ->whereIn('risk_level', ['high', 'critical'])
            ->orderByDesc('overall_risk')
            ->limit($limit)
            ->get();
    }

    protected function buildRecommendations(array $factors): array
    {
        $rec = [];
        if (in_array('low_attendance', $factors, true)) {
            $rec[] = 'Hubungi parent untuk mengkonfirmasi alasan absen siswa';
        }
        if (in_array('low_academic', $factors, true)) {
            $rec[] = 'Sesi remedial dengan wali kelas / mata pelajaran terkait';
        }
        if (in_array('discipline_issues', $factors, true)) {
            $rec[] = 'Konseling BP/BK dan parent meeting';
        }
        return $rec;
    }
}
