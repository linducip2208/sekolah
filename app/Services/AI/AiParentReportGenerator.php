<?php

namespace App\Services\AI;

use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Models\AI\AiUsageLog;
use App\Models\Academic\Student;
use App\Models\Academic\Attendance;
use App\Models\Discipline\DisciplineRecord;
use App\Models\Finance\FeeInvoice;
use App\Models\Achievement\StudentAchievement;
use App\Models\Counseling\CounselingSession;
use Illuminate\Support\Facades\DB;

class AiParentReportGenerator
{
    public function __construct(protected AiAdapterFactory $factory) {}

    public function generate(
        int $schoolId,
        int $userId,
        int $studentId,
        string $semester,
        ?string $language = 'id',
        ?int $providerId = null,
        ?int $modelId = null,
    ): array {
        $student = Student::where('school_id', $schoolId)
            ->with(['user:id,name', 'classSection.classRoom', 'parents'])
            ->findOrFail($studentId);

        $model = $this->resolveModel($schoolId, $modelId);
        $provider = $providerId
            ? AiProvider::where('school_id', $schoolId)->where('id', $providerId)->where('is_active', true)->firstOrFail()
            : $model->provider;

        if (!$provider || !$provider->is_active) {
            throw new \RuntimeException('AI provider tidak aktif.');
        }

        $factors = $this->gatherStudentData($schoolId, $studentId, $semester);
        $adapter = $this->factory->for($provider, $model);
        $messages = $this->buildPrompt($factors, $student, $semester, $language);

        $start = microtime(true);
        $result = $error = null;

        try {
            $result = $adapter->chat($messages, ['temperature' => 0.5, 'max_tokens' => 4096]);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            throw $e;
        } finally {
            $latencyMs = (int) round((microtime(true) - $start) * 1000);
            $cost = $this->estimateCost($model, $result['input_tokens'] ?? 0, $result['output_tokens'] ?? 0);
            AiUsageLog::create([
                'school_id'      => $schoolId,
                'user_id'        => $userId,
                'ai_model_id'    => $model->id,
                'feature_key'    => 'parent_report',
                'input_tokens'   => $result['input_tokens'] ?? 0,
                'output_tokens'  => $result['output_tokens'] ?? 0,
                'estimated_cost' => $cost,
                'latency_ms'     => $latencyMs,
                'success'        => $error === null,
                'error'          => $error,
            ]);
        }

        $parsed = $this->parseResult($result['text'] ?? '');

        return [
            'parsed'             => $parsed,
            'raw_text'           => $result['text'] ?? '',
            'student'            => $student,
            'factors'            => $factors,
            'ai_provider_id'     => $provider->id,
            'ai_model_id'        => $model->id,
            'tokens_used'        => ($result['input_tokens'] ?? 0) + ($result['output_tokens'] ?? 0),
            'processing_time_ms' => $latencyMs,
        ];
    }

    protected function gatherStudentData(int $schoolId, int $studentId, string $semester): array
    {
        $student = Student::where('school_id', $schoolId)->where('id', $studentId)->first();
        $studentName = $student?->user?->name ?? "Siswa #{$studentId}";

        $totalDays = Attendance::where('school_id', $schoolId)
            ->where('student_id', $studentId)->count();
        $presentDays = Attendance::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->whereIn('status', ['present', 'late'])->count();
        $lateDays = Attendance::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->where('status', 'late')->count();
        $absentDays = Attendance::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->where('status', 'absent')->count();
        $sickDays = Attendance::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->where('status', 'sick')->count();
        $excusedDays = Attendance::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->where('status', 'excused')->count();

        $attendancePct = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 100;

        $marksData = DB::table('marks')
            ->where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->selectRaw('subject_id, AVG((obtained_marks / NULLIF(total_marks, 0)) * 100) as avg_pct, COUNT(*) as exam_count')
            ->groupBy('subject_id')
            ->get();

        $disciplineCount = DisciplineRecord::where('school_id', $schoolId)
            ->where('student_id', $studentId)->count();
        $disciplinePositive = DisciplineRecord::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->where('points', '>', 0)->count();
        $disciplineNegative = DisciplineRecord::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->where('points', '<', 0)->count();

        $achievements = StudentAchievement::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->with('category:id,name')
            ->orderByDesc('achieved_at')
            ->limit(5)
            ->get();

        $clinicVisits = DB::table('clinic_visits')
            ->where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->count();

        $counselingCount = CounselingSession::where('school_id', $schoolId)
            ->where('student_id', $studentId)->count();

        $overdueInvoices = FeeInvoice::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->where('status', 'unpaid')
            ->where('due_date', '<', now())->count();

        return [
            'student_name'       => $studentName,
            'semester'           => $semester,
            'attendance' => [
                'total_days'    => $totalDays,
                'present_days'  => $presentDays,
                'late_days'     => $lateDays,
                'absent_days'   => $absentDays,
                'sick_days'     => $sickDays,
                'excused_days'  => $excusedDays,
                'percentage'    => $attendancePct,
            ],
            'academic' => [
                'subjects' => $marksData->map(fn ($m) => [
                    'avg_percentage' => round($m->avg_pct ?? 0, 1),
                    'exam_count'     => $m->exam_count,
                ])->toArray(),
            ],
            'discipline' => [
                'total'     => $disciplineCount,
                'positive'  => $disciplinePositive,
                'negative'  => $disciplineNegative,
            ],
            'achievements'     => $achievements->pluck('title')->toArray(),
            'clinic_visits'    => $clinicVisits,
            'counseling_count' => $counselingCount,
            'overdue_invoices' => $overdueInvoices,
        ];
    }

    protected function buildPrompt(array $factors, Student $student, string $semester, string $language): array
    {
        $langLabel = $language === 'id' ? 'Bahasa Indonesia' : 'English';
        $factorsJson = json_encode($factors, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $system = <<<PROMPT
Anda adalah konselor pendidikan profesional yang membuat laporan perkembangan siswa untuk orang tua/wali.
Buatkan laporan perkembangan siswa yang komprehensif, personal, dan mudah dipahami oleh orang tua.
Bahasa yang digunakan: {$langLabel}.

Response HARUS dalam format JSON:
{
  "report_title": "Laporan Perkembangan Siswa - Semester ...",
  "student_name": "...",
  "semester": "...",
  "academic_performance": {
    "summary": "<ringkasan performa akademik 3-5 kalimat>",
    "subject_highlights": ["<mata pelajaran: performa & catatan>"],
    "strengths": ["<kekuatan akademik>"],
    "areas_for_improvement": ["<area yang perlu diperbaiki>"]
  },
  "attendance_summary": {
    "summary": "<ringkasan kehadiran 2-3 kalimat>",
    "total_days": <angka>,
    "present_days": <angka>,
    "late_days": <angka>,
    "absent_days": <angka>,
    "percentage": <angka>
  },
  "behavioral_observations": {
    "summary": "<pengamatan perilaku 2-3 kalimat>",
    "discipline_points": <angka>,
    "positive_behaviors": ["<perilaku positif>"],
    "concerns": ["<perhatian>"]
  },
  "highlights": ["<prestasi positif / momen terbaik>"],
  "recommendations": [
    {
      "area": "<bidang>",
      "action": "<aksi konkret>",
      "by_whom": "<siapa yang melakukan>"
    }
  ],
  "overall_assessment": "<penilaian keseluruhan 3-5 kalimat>",
  "encouragement_message": "<pesan motivasi untuk siswa>"
}
Pastikan JSON valid. Ton: positif, konstruktif, dan supportive.
PROMPT;

        $user = "Data Siswa Semester {$semester}:\n{$factorsJson}\n\n"
            . "Buatkan laporan perkembangan yang komprehensif untuk orang tua/wali.";

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ];
    }

    protected function parseResult(string $raw): array
    {
        $trimmed = trim($raw);
        if (preg_match('/\{[\s\S]*\}/', $trimmed, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) return $decoded;
        }
        return ['raw' => $raw];
    }

    protected function estimateCost(AiModel $model, int $in, int $out): float
    {
        return round(($in / 1000) * (float) $model->input_price_per_1k + ($out / 1000) * (float) $model->output_price_per_1k, 6);
    }

    protected function resolveModel(int $schoolId, ?int $modelId): AiModel
    {
        if ($modelId) {
            return AiModel::where('school_id', $schoolId)->where('id', $modelId)->where('is_active', true)->firstOrFail();
        }
        return AiModel::where('school_id', $schoolId)->where('is_active', true)
            ->whereHas('provider', fn ($q) => $q->where('is_active', true))
            ->orderBy('priority')->firstOrFail();
    }
}
