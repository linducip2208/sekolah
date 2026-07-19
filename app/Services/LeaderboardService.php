<?php

namespace App\Services;

use App\Models\Academic\LeaderboardConfig;
use App\Models\Academic\Student;
use App\Models\Academic\StudentPoint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeaderboardService
{
    public function getConfig(int $schoolId, string $configType = 'monthly'): ?LeaderboardConfig
    {
        return LeaderboardConfig::where('school_id', $schoolId)
            ->where('config_type', $configType)
            ->where('is_active', true)
            ->first();
    }

    public function saveConfig(int $schoolId, string $configType, array $data): LeaderboardConfig
    {
        return LeaderboardConfig::updateOrCreate(
            ['school_id' => $schoolId, 'config_type' => $configType],
            [
                'is_active'              => $data['is_active'] ?? true,
                'weight_academic'        => $data['weight_academic'] ?? 30,
                'weight_attendance'      => $data['weight_attendance'] ?? 25,
                'weight_extracurricular' => $data['weight_extracurricular'] ?? 20,
                'weight_discipline'      => $data['weight_discipline'] ?? 25,
            ]
        );
    }

    public function getPeriodRange(string $configType): array
    {
        return match ($configType) {
            'weekly' => [now()->startOfWeek(), now()->endOfWeek()],
            'monthly' => [now()->startOfMonth(), now()->endOfMonth()],
            'semester' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    public function calculateRankings(int $schoolId, string $configType = 'monthly', ?int $classSectionId = null, int $cacheMinutes = 30): array
    {
        $cacheKey = "leaderboard:{$schoolId}:{$configType}:{$classSectionId}";

        return Cache::remember($cacheKey, now()->addMinutes($cacheMinutes), function () use ($schoolId, $configType, $classSectionId) {
            $config = $this->getConfig($schoolId, $configType);
            $weights = $config ? [
                'academic'        => $config->weight_academic,
                'attendance'      => $config->weight_attendance,
                'extracurricular' => $config->weight_extracurricular,
                'discipline'      => $config->weight_discipline,
            ] : [
                'academic'        => 30,
                'attendance'      => 25,
                'extracurricular' => 20,
                'discipline'      => 25,
            ];

            [$periodStart, $periodEnd] = $this->getPeriodRange($configType);

            $studentsQuery = Student::where('school_id', $schoolId)
                ->with('user:id,name,email');

            if ($classSectionId) {
                $studentsQuery->where('class_section_id', $classSectionId);
            }

            $students = $studentsQuery->get();

            $totalStudentPoints = StudentPoint::where('school_id', $schoolId)
                ->whereBetween('awarded_at', [$periodStart, $periodEnd])
                ->whereNull('deleted_at')
                ->count();

            $useFallback = $totalStudentPoints === 0;

            $rankings = [];

            foreach ($students as $student) {
                if ($useFallback) {
                    $academicScore = $this->fallbackAcademicScore($student->id, $schoolId);
                    $attendanceScore = $this->fallbackAttendanceScore($student->id, $schoolId, $periodStart, $periodEnd);
                    $extracurricularScore = 0;
                    $disciplineScore = $this->fallbackDisciplineScore($student->id, $schoolId, $periodStart, $periodEnd);
                } else {
                    $academicScore = StudentPoint::where('student_id', $student->id)
                        ->where('point_type', 'academic')
                        ->whereBetween('awarded_at', [$periodStart, $periodEnd])
                        ->whereNull('deleted_at')
                        ->sum('points');

                    $attendanceScore = StudentPoint::where('student_id', $student->id)
                        ->where('point_type', 'attendance')
                        ->whereBetween('awarded_at', [$periodStart, $periodEnd])
                        ->whereNull('deleted_at')
                        ->sum('points');

                    $extracurricularScore = StudentPoint::where('student_id', $student->id)
                        ->where('point_type', 'extracurricular')
                        ->whereBetween('awarded_at', [$periodStart, $periodEnd])
                        ->whereNull('deleted_at')
                        ->sum('points');

                    $disciplineScore = StudentPoint::where('student_id', $student->id)
                        ->where('point_type', 'discipline')
                        ->whereBetween('awarded_at', [$periodStart, $periodEnd])
                        ->whereNull('deleted_at')
                        ->sum('points');
                }

                $weightedScore = ($academicScore * $weights['academic'] / 100)
                    + ($attendanceScore * $weights['attendance'] / 100)
                    + ($extracurricularScore * $weights['extracurricular'] / 100)
                    + ($disciplineScore * $weights['discipline'] / 100);

                $totalRawScore = $academicScore + $attendanceScore + $extracurricularScore + $disciplineScore;

                if ($totalRawScore > 0 || $weightedScore > 0) {
                    $rankings[] = [
                        'student_id'          => $student->id,
                        'student_name'        => $student->user?->name ?? 'Tanpa Nama',
                        'class_section'       => $student->classSection?->name ?? '—',
                        'raw_points'          => $totalRawScore,
                        'weighted_score'      => round($weightedScore, 2),
                        'academic_points'     => $academicScore,
                        'attendance_points'   => $attendanceScore,
                        'extracurricular_points' => $extracurricularScore,
                        'discipline_points'   => $disciplineScore,
                        'avatar_url'          => null,
                        'class_section_id'    => $student->class_section_id,
                    ];
                }
            }

            usort($rankings, fn ($a, $b) => $b['weighted_score'] <=> $a['weighted_score']);

            foreach ($rankings as $i => &$entry) {
                $entry['rank'] = $i + 1;
            }

            return $rankings;
        });
    }

    private function fallbackAcademicScore(int $studentId, int $schoolId): int
    {
        $result = DB::table('marks')
            ->where('student_id', $studentId)
            ->where('school_id', $schoolId)
            ->where('total_marks', '>', 0)
            ->selectRaw('AVG(obtained_marks / total_marks * 100) as avg_pct')
            ->first();

        if (! $result || $result->avg_pct === null) {
            return 0;
        }

        return (int) round($result->avg_pct * 10);
    }

    private function fallbackAttendanceScore(int $studentId, int $schoolId, string $periodStart, string $periodEnd): int
    {
        $result = DB::table('attendances')
            ->where('student_id', $studentId)
            ->where('school_id', $schoolId)
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->whereNull('deleted_at')
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status IN (\'present\',\'late\') THEN 1 ELSE 0 END) as present')
            ->first();

        if (! $result || $result->total === 0) {
            return 0;
        }

        $pct = ($result->present / $result->total) * 100;

        return (int) round($pct * 10);
    }

    private function fallbackDisciplineScore(int $studentId, int $schoolId, string $periodStart, string $periodEnd): int
    {
        $total = DB::table('discipline_records')
            ->where('student_id', $studentId)
            ->where('school_id', $schoolId)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->whereNull('deleted_at')
            ->sum('points');

        return (int) $total;
    }

    public function syncPointsFromSources(int $schoolId): array
    {
        $students = Student::where('school_id', $schoolId)->get();

        $syncedCount = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($students as $student) {
                $academicScore = $this->fallbackAcademicScore($student->id, $schoolId);
                if ($academicScore > 0) {
                    StudentPoint::create([
                        'school_id'  => $schoolId,
                        'student_id' => $student->id,
                        'points'     => $academicScore,
                        'point_type' => 'academic',
                        'reason'     => 'Sinkronisasi otomatis dari data nilai akademik',
                        'awarded_at' => now(),
                    ]);
                    $syncedCount++;
                }

                [$periodStart, $periodEnd] = $this->getPeriodRange('monthly');
                $attendanceScore = $this->fallbackAttendanceScore($student->id, $schoolId, $periodStart, $periodEnd);
                if ($attendanceScore > 0) {
                    StudentPoint::create([
                        'school_id'  => $schoolId,
                        'student_id' => $student->id,
                        'points'     => $attendanceScore,
                        'point_type' => 'attendance',
                        'reason'     => 'Sinkronisasi otomatis dari data kehadiran',
                        'awarded_at' => now(),
                    ]);
                    $syncedCount++;
                }

                $disciplineScore = $this->fallbackDisciplineScore($student->id, $schoolId, $periodStart, $periodEnd);
                if ($disciplineScore !== 0) {
                    StudentPoint::create([
                        'school_id'  => $schoolId,
                        'student_id' => $student->id,
                        'points'     => $disciplineScore,
                        'point_type' => 'discipline',
                        'reason'     => 'Sinkronisasi otomatis dari data catatan disiplin',
                        'awarded_at' => now(),
                    ]);
                    $syncedCount++;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $errors[] = $e->getMessage();
        }

        $this->flushCache($schoolId);

        return ['synced' => $syncedCount, 'errors' => $errors];
    }

    public function getStudentRanking(int $schoolId, int $studentId, string $configType = 'monthly'): ?array
    {
        $rankings = $this->calculateRankings($schoolId, $configType);

        foreach ($rankings as $entry) {
            if ($entry['student_id'] === $studentId) {
                return $entry;
            }
        }

        return null;
    }

    public function awardPoints(int $schoolId, int $studentId, int $points, string $pointType, string $reason, ?string $referenceType = null, ?int $referenceId = null, ?int $awardedBy = null): StudentPoint
    {
        $record = StudentPoint::create([
            'school_id'      => $schoolId,
            'student_id'     => $studentId,
            'points'         => $points,
            'reason'         => $reason,
            'point_type'     => $pointType,
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'awarded_by'     => $awardedBy ?? auth()->id(),
            'awarded_at'     => now(),
        ]);

        $this->flushCache($schoolId);

        return $record;
    }

    public function deductPoints(int $schoolId, int $studentId, int $points, string $pointType, string $reason, ?string $referenceType = null, ?int $referenceId = null, ?int $awardedBy = null): StudentPoint
    {
        return $this->awardPoints($schoolId, $studentId, -abs($points), $pointType, $reason, $referenceType, $referenceId, $awardedBy);
    }

    public function flushCache(int $schoolId): void
    {
        $types = ['weekly', 'monthly', 'semester'];
        foreach ($types as $type) {
            Cache::forget("leaderboard:{$schoolId}:{$type}:");
            $classSectionIds = \App\Models\Academic\ClassSection::where('school_id', $schoolId)->pluck('id');
            foreach ($classSectionIds as $csId) {
                Cache::forget("leaderboard:{$schoolId}:{$type}:{$csId}");
            }
            Cache::forget("leaderboard:{$schoolId}:{$type}:" . null);
        }
    }
}
