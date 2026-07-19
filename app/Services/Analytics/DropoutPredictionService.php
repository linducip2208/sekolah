<?php

namespace App\Services\Analytics;

use App\Models\Academic\Attendance;
use App\Models\Academic\Student;
use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Models\AI\AiUsageLog;
use App\Models\Analytics\AiDropoutPrediction;
use App\Models\Discipline\DisciplineRecord;
use App\Models\Finance\FeeInvoice;
use App\Services\AI\AiAdapterFactory;
use Illuminate\Support\Facades\DB;

class DropoutPredictionService
{
    public function __construct(protected AiAdapterFactory $factory) {}

    public function predictForSchool(int $schoolId, ?int $providerId = null, ?int $modelId = null): int
    {
        $count = 0;
        Student::where('school_id', $schoolId)->chunk(200, function ($students) use ($schoolId, $providerId, $modelId, &$count) {
            foreach ($students as $student) {
                try {
                    $this->predictForStudent($schoolId, $student->id, $providerId, $modelId);
                    $count++;
                } catch (\Throwable $e) {
                    \Log::error('Dropout prediction failed for student ' . $student->id, [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });
        return $count;
    }

    public function predictForStudent(int $schoolId, int $studentId, ?int $providerId = null, ?int $modelId = null): AiDropoutPrediction
    {
        $factors = $this->gatherFactors($schoolId, $studentId);

        $model    = $this->resolveModel($schoolId, $modelId);
        $provider = $providerId
            ? AiProvider::where('school_id', $schoolId)->where('id', $providerId)->where('is_active', true)->firstOrFail()
            : $model->provider;

        if (!$provider || !$provider->is_active) {
            throw new \RuntimeException('AI provider tidak aktif.');
        }

        $adapter = $this->factory->for($provider, $model);
        $messages = $this->buildPrompt($factors);
        $systemUserId = DB::table('users')->where('school_id', $schoolId)->value('id') ?? 1;

        $start  = microtime(true);
        $result = null;
        $error  = null;

        try {
            $result = $adapter->chat($messages, [
                'temperature' => 0.3,
                'max_tokens'  => 1024,
                'json_mode'   => true,
            ]);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            throw $e;
        } finally {
            $latencyMs = (int) round((microtime(true) - $start) * 1000);
            $tokens    = ($result['input_tokens'] ?? 0) + ($result['output_tokens'] ?? 0);
            $cost      = $this->estimateCost($model, $result['input_tokens'] ?? 0, $result['output_tokens'] ?? 0);

            AiUsageLog::create([
                'school_id'      => $schoolId,
                'user_id'        => $systemUserId,
                'ai_model_id'    => $model->id,
                'feature_key'    => 'dropout_prediction',
                'input_tokens'   => $result['input_tokens'] ?? 0,
                'output_tokens'  => $result['output_tokens'] ?? 0,
                'estimated_cost' => $cost,
                'latency_ms'     => $latencyMs,
                'success'        => $error === null,
                'error'          => $error,
            ]);
        }

        $parsed = $this->parseResponse($result['text'] ?? '');

        return AiDropoutPrediction::create([
            'school_id'            => $schoolId,
            'student_id'           => $studentId,
            'prediction_date'      => today(),
            'risk_level'           => $parsed['risk_level'],
            'risk_score'           => $parsed['risk_score'],
            'contributing_factors' => $factors,
            'ai_analysis'          => $parsed['analysis'],
            'ai_provider_id'       => $provider->id,
            'ai_model_id'          => $model->id,
            'recommended_actions'  => $parsed['recommendations'],
            'notified_parents'     => false,
            'notified_teacher'     => false,
            'tokens_used'          => $tokens,
            'processing_time_ms'   => $latencyMs,
        ]);
    }

    protected function gatherFactors(int $schoolId, int $studentId): array
    {
        $sinceDate = now()->subDays(30);

        $totalAttendance = Attendance::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->where('date', '>=', $sinceDate)
            ->count();
        $presentCount = Attendance::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->where('date', '>=', $sinceDate)
            ->whereIn('status', ['present', 'late'])
            ->count();
        $attendancePct = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 100;

        $avgMarkPct = (float) DB::table('marks')
            ->where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw('AVG((obtained_marks / NULLIF(total_marks, 0)) * 100) as avg_pct')
            ->value('avg_pct') ?? 75;
        $avgMarkPct = round($avgMarkPct, 1);

        $disciplineCount = DisciplineRecord::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->where('incident_date', '>=', now()->subMonths(6))
            ->count();

        $counselingCount = DB::table('counseling_sessions')
            ->where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->where('session_date', '>=', now()->subMonths(6))
            ->count();

        $unpaidInvoices = FeeInvoice::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->where('status', 'unpaid')
            ->where('due_date', '<', now())
            ->count();

        $studentName = Student::where('school_id', $schoolId)
            ->where('id', $studentId)
            ->with('user:id,name')
            ->first();
        $name = $studentName?->user?->name ?? "Siswa #{$studentId}";

        return [
            'student_name'      => $name,
            'attendance_pct'    => $attendancePct,
            'avg_mark_pct'      => $avgMarkPct,
            'discipline_count'  => $disciplineCount,
            'counseling_count'  => $counselingCount,
            'unpaid_invoices'   => $unpaidInvoices,
        ];
    }

    protected function buildPrompt(array $factors): array
    {
        $systemPrompt = <<<PROMPT
Anda adalah analis pendidikan yang membantu sekolah mengidentifikasi siswa berisiko putus sekolah (dropout).
Analisis data siswa berikut dan berikan prediksi risiko dalam Bahasa Indonesia.
Response HARUS dalam format JSON:
{
  "risk_score": <angka 0-100, 0=aman, 100=sangat berisiko>,
  "risk_level": "<low|medium|high|critical>",
  "analysis": "<analisis naratif 3-5 kalimat dalam Bahasa Indonesia>",
  "recommendations": "<rekomendasi aksi 2-4 poin, dipisahkan titik koma, dalam Bahasa Indonesia>"
}
PROMPT;

        $userMessage = "Data Siswa:\n"
            . "- Nama: {$factors['student_name']}\n"
            . "- Persentase Kehadiran (30 hari): {$factors['attendance_pct']}%\n"
            . "- Rata-rata Nilai (12 bulan): {$factors['avg_mark_pct']}%\n"
            . "- Jumlah Catatan Disiplin (6 bulan): {$factors['discipline_count']}\n"
            . "- Sesi Konseling (6 bulan): {$factors['counseling_count']}\n"
            . "- Tagihan SPP Tertunggak: {$factors['unpaid_invoices']}\n\n"
            . "Berikan analisis risiko dropout untuk siswa ini.";

        return [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage],
        ];
    }

    protected function parseResponse(string $raw): array
    {
        $json = $this->extractJson($raw);

        $riskScore  = max(0, min(100, (float) ($json['risk_score'] ?? 30)));
        $riskLevel  = (string) ($json['risk_level'] ?? 'low');
        if (!in_array($riskLevel, ['low', 'medium', 'high', 'critical'], true)) {
            $riskLevel = match (true) {
                $riskScore >= 70 => 'critical',
                $riskScore >= 50 => 'high',
                $riskScore >= 30 => 'medium',
                default          => 'low',
            };
        }

        return [
            'risk_score'      => $riskScore,
            'risk_level'      => $riskLevel,
            'analysis'        => (string) ($json['analysis'] ?? 'Tidak ada analisis.'),
            'recommendations' => (string) ($json['recommendations'] ?? ''),
        ];
    }

    protected function extractJson(string $raw): array
    {
        $trimmed = trim($raw);
        if (str_starts_with($trimmed, '{')) {
            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) return $decoded;
        }
        if (preg_match('/\{[\s\S]*\}/', $trimmed, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) return $decoded;
        }
        return [
            'risk_score'  => 30,
            'risk_level'  => 'low',
            'analysis'    => $raw,
            'recommendations' => '',
        ];
    }

    protected function resolveModel(int $schoolId, ?int $modelId): AiModel
    {
        if ($modelId) {
            return AiModel::where('school_id', $schoolId)
                ->where('id', $modelId)
                ->where('is_active', true)
                ->firstOrFail();
        }
        return AiModel::where('school_id', $schoolId)
            ->where('is_active', true)
            ->whereHas('provider', fn ($q) => $q->where('is_active', true))
            ->orderBy('priority')
            ->firstOrFail();
    }

    protected function estimateCost(AiModel $model, int $inputTokens, int $outputTokens): float
    {
        $inCost  = ($inputTokens / 1000) * (float) $model->input_price_per_1k;
        $outCost = ($outputTokens / 1000) * (float) $model->output_price_per_1k;
        return round($inCost + $outCost, 6);
    }
}
