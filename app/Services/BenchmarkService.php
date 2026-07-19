<?php

namespace App\Services;

use App\Models\Analytics\BenchmarkMetric;
use App\Models\Analytics\BenchmarkResult;
use App\Models\Foundation\Foundation;
use App\Models\School;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BenchmarkService
{
    public function computeMetricForSchool(int $schoolId, BenchmarkMetric $metric, string $period): array
    {
        $value = $this->executeMetricQuery($metric, $schoolId, $period);

        if ($value === null) {
            return ['error' => "Gagal menghitung metric {$metric->metric_key}"];
        }

        BenchmarkResult::updateOrCreate(
            [
                'school_id'           => $schoolId,
                'benchmark_metric_id' => $metric->id,
                'period'              => $period,
            ],
            [
                'value'         => $value,
                'calculated_at' => now(),
            ]
        );

        return ['success' => true, 'value' => $value];
    }

    public function computeAllForSchool(int $schoolId, string $period): array
    {
        $metrics = BenchmarkMetric::where('is_active', true)->get();
        $results = [];

        foreach ($metrics as $metric) {
            $results[$metric->metric_key] = $this->computeMetricForSchool($schoolId, $metric, $period);
        }

        return $results;
    }

    public function computeAndRankForFoundation(int $foundationId, string $period): array
    {
        $foundation = Foundation::findOrFail($foundationId);
        $schoolIds = $foundation->schools()->where('is_active', true)->pluck('schools.id')->toArray();
        $metrics = BenchmarkMetric::where('is_active', true)->get();
        $results = [];

        foreach ($metrics as $metric) {
            $values = [];
            foreach ($schoolIds as $schoolId) {
                $value = $this->executeMetricQuery($metric, $schoolId, $period);
                if ($value !== null) {
                    $values[$schoolId] = $value;

                    BenchmarkResult::updateOrCreate(
                        [
                            'school_id'           => $schoolId,
                            'benchmark_metric_id' => $metric->id,
                            'period'              => $period,
                        ],
                        ['value' => $value, 'calculated_at' => now()]
                    );
                }
            }

            $this->rankResults($metric->id, $period, $values);
            $results[$metric->metric_key] = $values;
        }

        return $results;
    }

    public function getFoundationDashboard(int $foundationId, string $period): array
    {
        $foundation = Foundation::with('schools')->findOrFail($foundationId);
        $schoolIds = $foundation->schools->pluck('id')->toArray();

        $metrics = BenchmarkMetric::where('is_active', true)->get();

        $results = BenchmarkResult::whereIn('school_id', $schoolIds)
            ->where('period', $period)
            ->with('benchmarkMetric')
            ->get()
            ->groupBy('benchmark_metric_id');

        $schoolsData = [];
        foreach ($foundation->schools as $school) {
            $schoolsData[$school->id] = ['name' => $school->name, 'metrics' => []];
        }

        $radarData = ['labels' => [], 'datasets' => []];
        $metricList = [];

        foreach ($metrics as $metric) {
            $radarData['labels'][] = $metric->metric_name;
            $schoolMetrics = $results->get($metric->id, collect());
            $averages = [];

            foreach ($schoolIds as $sid) {
                $schoolResult = $schoolMetrics->firstWhere('school_id', $sid);
                if ($schoolResult) {
                    $schoolsData[$sid]['metrics'][$metric->metric_key] = [
                        'value'      => $schoolResult->value,
                        'rank'       => $schoolResult->rank,
                        'percentile' => $schoolResult->percentile,
                    ];
                    $averages[] = $schoolResult->value;
                } else {
                    $schoolsData[$sid]['metrics'][$metric->metric_key] = [
                        'value' => null, 'rank' => null, 'percentile' => null,
                    ];
                }
            }

            $avgVal = count($averages) > 0 ? array_sum($averages) / count($averages) : 0;

            $metricList[] = [
                'metric_key'  => $metric->metric_key,
                'metric_name' => $metric->metric_name,
                'unit'        => $metric->unit,
                'description' => $metric->description,
                'average'     => round($avgVal, 2),
                'schools'     => $schoolsData,
            ];
        }

        $colors = [
            'rgba(37,99,235,0.4)', 'rgba(184,134,11,0.4)', 'rgba(16,185,129,0.4)',
            'rgba(234,179,8,0.4)', 'rgba(220,38,38,0.4)', 'rgba(147,51,234,0.4)',
            'rgba(14,165,233,0.4)', 'rgba(245,158,11,0.4)', 'rgba(34,197,94,0.4)',
            'rgba(139,92,246,0.4)',
        ];

        foreach ($schoolIds as $i => $sid) {
            $name = $foundation->schools->firstWhere('id', $sid)?->name ?? "Sekolah {$sid}";
            $schoolResultData = $results->flatMap(fn($r) => $r->where('school_id', $sid))->keyBy('benchmark_metric_id');
            $data = $metrics->map(fn($m) => (float) ($schoolResultData->get($m->id)?->value ?? 0))->toArray();

            $radarData['datasets'][] = [
                'label'           => $name,
                'data'            => $data,
                'backgroundColor' => $colors[$i % count($colors)],
                'borderColor'     => str_replace('0.4', '1', $colors[$i % count($colors)]),
                'borderWidth'     => 2,
                'pointBackgroundColor' => str_replace('0.4', '1', $colors[$i % count($colors)]),
            ];
        }

        $schoolResultsForRanking = BenchmarkResult::whereIn('school_id', $schoolIds)
            ->where('period', $period)
            ->get()
            ->groupBy('benchmark_metric_id');

        $rankings = $foundation->schools->map(function ($school) use ($schoolResultsForRanking, $metrics) {
            $totalRank = 0;
            $metricCount = 0;

            foreach ($metrics as $metric) {
                $metricResults = $schoolResultsForRanking->get($metric->id, collect());
                $schoolResult = $metricResults->firstWhere('school_id', $school->id);
                if ($schoolResult && $schoolResult->rank) {
                    $totalRank += $schoolResult->rank;
                    $metricCount++;
                }
            }

            $avgRank = $metricCount > 0 ? round($totalRank / $metricCount, 1) : null;

            return [
                'school_id'   => $school->id,
                'school_name' => $school->name,
                'avg_rank'    => $avgRank,
                'metrics'     => $schoolResultsForRanking
                    ->flatMap(fn($rs) => $rs->where('school_id', $school->id))
                    ->values(),
            ];
        })->sortBy('avg_rank')->values();

        return [
            'foundation' => $foundation,
            'period'     => $period,
            'metrics'    => $metricList,
            'radar_data' => $radarData,
            'rankings'   => $rankings,
            'schools'    => $schoolsData,
        ];
    }

    public function getSchoolSelfComparison(int $schoolId, string $period): array
    {
        $school = School::findOrFail($schoolId);
        $foundations = $school->foundations()->pluck('foundations.id')->toArray();

        $results = BenchmarkResult::where('school_id', $schoolId)
            ->where('period', $period)
            ->with('benchmarkMetric')
            ->get()
            ->keyBy('benchmark_metric_id');

        $selfData = [];
        $avgData = [];

        foreach ($results as $metricId => $result) {
            $metric = $result->benchmarkMetric;

            $avg = BenchmarkResult::where('benchmark_metric_id', $metricId)
                ->where('period', $period)
                ->when(!empty($foundations), fn($q) => $q->whereIn('school_id', function ($sub) use ($foundations) {
                    $sub->select('school_id')
                        ->from('foundation_school_links')
                        ->whereIn('foundation_id', $foundations);
                }))
                ->avg('value');

            $selfData[$metric->metric_key] = [
                'metric_name' => $metric->metric_name,
                'value'       => $result->value,
                'rank'        => $result->rank,
                'percentile'  => $result->percentile,
                'unit'        => $metric->unit,
            ];

            $avgData[$metric->metric_key] = [
                'metric_name' => $metric->metric_name,
                'avg'         => $avg ? round($avg, 2) : null,
            ];
        }

        return [
            'school'    => $school,
            'period'    => $period,
            'self'      => $selfData,
            'average'   => $avgData,
            'metrics'   => BenchmarkMetric::where('is_active', true)->get(),
        ];
    }

    public function getHistoricalTrend(int $schoolId, string $metricKey, int $months = 12): array
    {
        $metric = BenchmarkMetric::where('metric_key', $metricKey)->firstOrFail();

        $results = BenchmarkResult::where('school_id', $schoolId)
            ->where('benchmark_metric_id', $metric->id)
            ->where('period', '>=', now()->subMonths($months)->format('Y-m'))
            ->orderBy('period')
            ->get();

        $avgResults = BenchmarkResult::where('benchmark_metric_id', $metric->id)
            ->where('period', '>=', now()->subMonths($months)->format('Y-m'))
            ->selectRaw('period, AVG(value) as avg_value, COUNT(*) as school_count')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $labels = [];
        $self = [];
        $avg = [];

        foreach ($results as $r) {
            $labels[] = \Carbon\Carbon::createFromFormat('Y-m', $r->period)->translatedFormat('M Y');
            $self[] = (float) $r->value;
            $avg[] = isset($avgResults[$r->period]) ? round((float) $avgResults[$r->period]->avg_value, 2) : null;
        }

        return [
            'metric' => $metric,
            'labels' => $labels,
            'datasets' => [
                [
                    'label'           => 'Sekolah Anda',
                    'data'            => $self,
                    'borderColor'     => 'rgba(37,99,235,1)',
                    'backgroundColor' => 'rgba(37,99,235,0.1)',
                    'fill'            => true,
                    'tension'         => 0.3,
                ],
                [
                    'label'           => 'Rata-rata Yayasan',
                    'data'            => $avg,
                    'borderColor'     => 'rgba(184,134,11,1)',
                    'backgroundColor' => 'rgba(184,134,11,0.1)',
                    'fill'            => true,
                    'tension'         => 0.3,
                    'borderDash'      => [5, 5],
                ],
            ],
        ];
    }

    public function seedDefaultMetrics(): void
    {
        $defaults = [
            ['metric_key' => 'avg_attendance_pct', 'metric_name' => 'Rata-rata Kehadiran', 'description' => 'Persentase kehadiran rata-rata seluruh siswa per bulan', 'unit' => 'percent', 'aggregation' => 'avg'],
            ['metric_key' => 'avg_exam_score', 'metric_name' => 'Rata-rata Nilai Ujian', 'description' => 'Rata-rata nilai ujian seluruh siswa per semester', 'unit' => 'score', 'aggregation' => 'avg'],
            ['metric_key' => 'fee_collection_rate', 'metric_name' => 'Tingkat Koleksi SPP', 'description' => 'Persentase SPP yang terkumpul dari total tagihan', 'unit' => 'percent', 'aggregation' => 'avg'],
            ['metric_key' => 'student_teacher_ratio', 'metric_name' => 'Rasio Siswa-Guru', 'description' => 'Jumlah siswa dibanding jumlah guru', 'unit' => 'ratio', 'aggregation' => 'avg'],
            ['metric_key' => 'dropout_rate', 'metric_name' => 'Angka Putus Sekolah', 'description' => 'Persentase siswa yang keluar/putus sekolah', 'unit' => 'percent', 'aggregation' => 'avg'],
            ['metric_key' => 'avg_discipline_score', 'metric_name' => 'Skor Disiplin Rata-rata', 'description' => 'Rata-rata poin disiplin per siswa (positif - negatif)', 'unit' => 'score', 'aggregation' => 'avg'],
            ['metric_key' => 'library_usage_rate', 'metric_name' => 'Tingkat Penggunaan Perpustakaan', 'description' => 'Rata-rata peminjaman buku per siswa per bulan', 'unit' => 'books_per_student', 'aggregation' => 'avg'],
            ['metric_key' => 'extracurricular_participation_pct', 'metric_name' => 'Partisipasi Ekstrakurikuler', 'description' => 'Persentase siswa yang ikut minimal 1 ekstrakurikuler', 'unit' => 'percent', 'aggregation' => 'avg'],
        ];

        foreach ($defaults as $data) {
            BenchmarkMetric::firstOrCreate(['metric_key' => $data['metric_key']], $data);
        }
    }

    private function executeMetricQuery(BenchmarkMetric $metric, int $schoolId, string $period): ?float
    {
        [$year, $month] = explode('-', $period);

        return match ($metric->metric_key) {
            'avg_attendance_pct' => $this->avgAttendancePct($schoolId, $year, $month),
            'avg_exam_score' => $this->avgExamScore($schoolId),
            'fee_collection_rate' => $this->feeCollectionRate($schoolId, $year, $month),
            'student_teacher_ratio' => $this->studentTeacherRatio($schoolId),
            'dropout_rate' => $this->dropoutRate($schoolId),
            'avg_discipline_score' => $this->avgDisciplineScore($schoolId, $year, $month),
            'library_usage_rate' => $this->libraryUsageRate($schoolId, $year, $month),
            'extracurricular_participation_pct' => $this->extracurricularParticipation($schoolId),
            default => null,
        };
    }

    private function avgAttendancePct(int $schoolId, string $year, string $month): float
    {
        $result = DB::table('attendances')
            ->where('school_id', $schoolId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status IN (\'present\',\'late\') THEN 1 ELSE 0 END) as present')
            ->first();

        return $result && $result->total > 0
            ? round(($result->present / $result->total) * 100, 2)
            : 0;
    }

    private function avgExamScore(int $schoolId): float
    {
        $result = DB::table('marks')
            ->where('school_id', $schoolId)
            ->where('total_marks', '>', 0)
            ->selectRaw('AVG(obtained_marks / total_marks * 100) as avg_score')
            ->first();

        return $result ? round((float) $result->avg_score, 2) : 0;
    }

    private function feeCollectionRate(int $schoolId, string $year, string $month): float
    {
        $result = DB::table('fee_invoices')
            ->where('school_id', $schoolId)
            ->whereYear('due_date', $year)
            ->whereMonth('due_date', $month)
            ->selectRaw('SUM(amount) as total, SUM(paid_amount) as paid')
            ->first();

        return $result && $result->total > 0
            ? round(($result->paid / $result->total) * 100, 2)
            : 0;
    }

    private function studentTeacherRatio(int $schoolId): float
    {
        $students = DB::table('students')->where('school_id', $schoolId)->count();
        $staff = DB::table('staff')->where('school_id', $schoolId)->where('type', 'teacher')->count();

        return $staff > 0 ? round($students / $staff, 2) : 0;
    }

    private function dropoutRate(int $schoolId): float
    {
        $total = DB::table('students')->where('school_id', $schoolId)->withTrashed()->count();
        $dropped = DB::table('students')->where('school_id', $schoolId)->onlyTrashed()->count();

        return $total > 0 ? round(($dropped / $total) * 100, 2) : 0;
    }

    private function avgDisciplineScore(int $schoolId, string $year, string $month): float
    {
        $result = DB::table('discipline_records')
            ->where('school_id', $schoolId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->selectRaw('AVG(points) as avg_points')
            ->first();

        return $result ? round((float) $result->avg_points, 2) : 0;
    }

    private function libraryUsageRate(int $schoolId, string $year, string $month): float
    {
        $studentCount = DB::table('students')->where('school_id', $schoolId)->count();
        $issueCount = DB::table('library_issues')
            ->where('school_id', $schoolId)
            ->whereYear('issue_date', $year)
            ->whereMonth('issue_date', $month)
            ->count();

        return $studentCount > 0 ? round($issueCount / $studentCount, 2) : 0;
    }

    private function extracurricularParticipation(int $schoolId): float
    {
        $totalStudents = DB::table('students')->where('school_id', $schoolId)->count();
        $participants = DB::table('extracurricular_enrollments')
            ->where('school_id', $schoolId)
            ->distinct('student_id')
            ->count('student_id');

        return $totalStudents > 0 ? round(($participants / $totalStudents) * 100, 2) : 0;
    }

    private function rankResults(int $metricId, string $period, array $values): void
    {
        if (empty($values)) {
            return;
        }

        arsort($values);
        $total = count($values);
        $rank = 1;

        foreach ($values as $schoolId => $value) {
            $percentile = $total > 1 ? round(($total - $rank) / ($total - 1) * 100, 2) : 100;

            BenchmarkResult::where([
                'school_id'           => $schoolId,
                'benchmark_metric_id' => $metricId,
                'period'              => $period,
            ])->update([
                'rank'       => $rank,
                'percentile' => $percentile,
            ]);

            $rank++;
        }
    }
}
