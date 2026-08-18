<?php

namespace App\Services\Analytics;

use App\Models\Academic\Student;
use App\Models\Academic\Attendance;
use App\Models\Discipline\DisciplineRecord;
use App\Models\Finance\FeeInvoice;
use App\Models\Counseling\CounselingSession;
use App\Models\Communication\Conversation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class PredictiveAnalyticsService
{
    protected array $weights = [
        'attendance'    => 0.30,
        'academic'      => 0.25,
        'discipline'    => 0.20,
        'engagement'    => 0.15,
        'financial'     => 0.10,
    ];

    public function predictDropoutRisk(int $schoolId): Collection
    {
        $students = Student::where('school_id', $schoolId)
            ->where('status', 'active')
            ->with('user:id,name')
            ->get();

        $results = collect();

        foreach ($students as $student) {
            $factors = $this->calculateRiskFactors($schoolId, $student);
            $riskScore = $this->computeWeightedScore($factors);
            $riskLevel = $this->scoreToLevel($riskScore);

            $results->push([
                'student_id'   => $student->id,
                'student_name' => $student->user?->name ?? 'N/A',
                'admission_no' => $student->admission_no,
                'risk_score'   => round($riskScore, 1),
                'risk_level'   => $riskLevel,
                'risk_factors' => $factors,
            ]);
        }

        return $results->sortByDesc('risk_score')->values();
    }

    public function calculateRiskFactors(int $schoolId, Student $student): array
    {
        $sinceDate = now()->subDays(90);

        $totalAttendance = Attendance::where('school_id', $schoolId)
            ->where('student_id', $student->id)
            ->where('date', '>=', $sinceDate)
            ->count();
        $presentCount = Attendance::where('school_id', $schoolId)
            ->where('student_id', $student->id)
            ->where('date', '>=', $sinceDate)
            ->whereIn('status', ['present', 'late'])
            ->count();
        $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 100;
        $attendanceScore = max(0, min(100, 100 - $attendanceRate));

        $avgMarkPct = (float) DB::table('marks')
            ->where('school_id', $schoolId)
            ->where('student_id', $student->id)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('AVG((obtained_marks / NULLIF(total_marks, 0)) * 100) as avg_pct')
            ->value('avg_pct') ?? 70;
        $academicScore = max(0, min(100, 100 - $avgMarkPct));

        $disciplineCount = DisciplineRecord::where('school_id', $schoolId)
            ->where('student_id', $student->id)
            ->where('incident_date', '>=', $sinceDate)
            ->count();
        $disciplineScore = min(100, $disciplineCount * 15);

        $messageCount = Conversation::where('school_id', $schoolId)
            ->whereHas('participants', fn ($q) => $q->where('user_id', $student->user_id))
            ->where('created_at', '>=', $sinceDate)
            ->count();
        $engagementScore = max(0, min(100, max(0, 80 - $messageCount * 5)));

        $overdueInvoices = FeeInvoice::where('school_id', $schoolId)
            ->where('student_id', $student->id)
            ->where('status', 'unpaid')
            ->where('due_date', '<', now())
            ->count();
        $financialScore = min(100, $overdueInvoices * 25);

        return [
            'attendance_rate'   => $attendanceRate,
            'attendance_score'  => round($attendanceScore, 1),
            'avg_mark_pct'      => round($avgMarkPct, 1),
            'academic_score'    => round($academicScore, 1),
            'discipline_count'  => $disciplineCount,
            'discipline_score'  => round($disciplineScore, 1),
            'engagement_score'  => round($engagementScore, 1),
            'overdue_invoices'  => $overdueInvoices,
            'financial_score'   => round($financialScore, 1),
        ];
    }

    protected function computeWeightedScore(array $factors): float
    {
        return (
            $factors['attendance_score'] * $this->weights['attendance']
            + $factors['academic_score'] * $this->weights['academic']
            + $factors['discipline_score'] * $this->weights['discipline']
            + $factors['engagement_score'] * $this->weights['engagement']
            + $factors['financial_score'] * $this->weights['financial']
        );
    }

    protected function scoreToLevel(float $score): string
    {
        return match (true) {
            $score >= 70 => 'critical',
            $score >= 50 => 'high',
            $score >= 30 => 'medium',
            default       => 'low',
        };
    }

    public function computeWeightedScorePublic(array $factors): float
    {
        return $this->computeWeightedScore($factors);
    }

    public function getRiskDistribution(int $schoolId): array
    {
        $results = $this->predictDropoutRisk($schoolId);

        return [
            'total'    => $results->count(),
            'low'      => $results->where('risk_level', 'low')->count(),
            'medium'   => $results->where('risk_level', 'medium')->count(),
            'high'     => $results->where('risk_level', 'high')->count(),
            'critical' => $results->where('risk_level', 'critical')->count(),
        ];
    }
}
