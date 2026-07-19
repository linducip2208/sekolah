<?php

namespace App\Services\AI;

use App\Models\AI\AiEssayGrading;
use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Models\AI\AiUsageLog;
use App\Services\AI\Contracts\AiAdapterInterface;

class EssayGradingService
{
    public function __construct(protected AiAdapterFactory $factory) {}

    public function grade(
        int $schoolId,
        int $userId,
        int $examId,
        int $studentId,
        string $questionText,
        string $studentAnswer,
        ?string $referenceAnswer,
        ?int $providerId,
        ?int $modelId,
        ?string $rubric = null,
    ): AiEssayGrading {
        $model   = $this->resolveModel($schoolId, $modelId);
        $provider = $providerId
            ? AiProvider::where('school_id', $schoolId)->where('id', $providerId)->where('is_active', true)->firstOrFail()
            : $model->provider;

        if (!$provider || !$provider->is_active) {
            throw new \RuntimeException('AI provider tidak aktif.');
        }

        $adapter = $this->factory->for($provider, $model);

        $messages = $this->buildPrompt($questionText, $studentAnswer, $referenceAnswer, $rubric);

        $start  = microtime(true);
        $result = null;
        $error  = null;

        try {
            $result = $adapter->chat($messages, [
                'temperature' => 0.3,
                'max_tokens'  => 1024,
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
                'user_id'        => $userId,
                'ai_model_id'    => $model->id,
                'feature_key'    => 'essay_grading',
                'input_tokens'   => $result['input_tokens'] ?? 0,
                'output_tokens'  => $result['output_tokens'] ?? 0,
                'estimated_cost' => $cost,
                'latency_ms'     => $latencyMs,
                'success'        => $error === null,
                'error'          => $error,
            ]);
        }

        $parsed = $this->parseResponse($result['text'] ?? '');

        return AiEssayGrading::create([
            'school_id'           => $schoolId,
            'exam_id'             => $examId,
            'student_id'          => $studentId,
            'question_text'       => $questionText,
            'student_answer'      => $studentAnswer,
            'reference_answer'    => $referenceAnswer,
            'ai_provider_id'      => $provider->id,
            'ai_model_id'         => $model->id,
            'ai_score'            => $parsed['score'],
            'ai_feedback'         => $parsed['feedback'],
            'ai_rubric_breakdown' => $parsed['rubric_breakdown'],
            'tokens_used'         => $tokens,
            'processing_time_ms'  => $latencyMs,
            'graded_by'           => $userId,
            'graded_at'           => now(),
        ]);
    }

    public function gradeBatch(
        int $schoolId,
        int $userId,
        int $examId,
        array $submissions,
        ?int $providerId,
        ?int $modelId,
        ?string $rubric = null,
    ): int {
        $count = 0;
        foreach ($submissions as $sub) {
            try {
                $this->grade(
                    $schoolId, $userId, $examId,
                    $sub['student_id'], $sub['question_text'], $sub['student_answer'],
                    $sub['reference_answer'] ?? null,
                    $providerId, $modelId, $rubric,
                );
                $count++;
            } catch (\Throwable $e) {
                \Log::error('Essay grading failed for student ' . ($sub['student_id'] ?? '?'), [
                    'error' => $e->getMessage(),
                ]);
            }
        }
        return $count;
    }

    protected function buildPrompt(
        string $question,
        string $studentAnswer,
        ?string $referenceAnswer,
        ?string $rubric,
    ): array {
        $refSection = $referenceAnswer
            ? "\n\nJawaban Referensi:\n{$referenceAnswer}"
            : '';

        $rubricSection = $rubric
            ? "\n\nRubrik Penilaian:\n{$rubric}"
            : $this->defaultRubric();

        $systemPrompt = <<<PROMPT
Anda adalah guru profesional di Indonesia. Tugas Anda menilai jawaban essay siswa secara objektif.
Berikan penilaian dalam Bahasa Indonesia dengan format JSON berikut:
{
  "score": <angka 0-100>,
  "feedback": "<feedback ringkas 2-4 kalimat dalam Bahasa Indonesia>",
  "rubric_breakdown": {
    "relevansi": <skor relevansi jawaban dengan pertanyaan 0-100>,
    "kelengkapan": <skor kelengkapan jawaban 0-100>,
    "struktur_logika": <skor struktur dan logika penulisan 0-100>,
    "tata_bahasa": <skor tata bahasa dan ejaan 0-100>,
    "kedalaman_analisis": <skor kedalaman analisis 0-100>
  }
}
Pastikan response HANYA JSON, tidak ada teks lain.
PROMPT;

        $userMessage = "Pertanyaan:\n{$question}{$refSection}{$rubricSection}\n\nJawaban Siswa:\n{$studentAnswer}\n\nSilakan nilai jawaban ini.";

        return [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage],
        ];
    }

    protected function defaultRubric(): string
    {
        return <<<RUBRIC
Kriteria Penilaian (masing-masing 0-20 poin):
1. Relevansi: Apakah jawaban sesuai dengan pertanyaan?
2. Kelengkapan: Apakah semua aspek pertanyaan terjawab?
3. Struktur & Logika: Apakah argumen tersusun secara logis?
4. Tata Bahasa: Apakah menggunakan bahasa Indonesia yang baik dan benar?
5. Kedalaman Analisis: Apakah jawaban menunjukkan pemahaman mendalam?
Total: 100 poin.
RUBRIC;
    }

    protected function parseResponse(string $raw): array
    {
        $json = $this->extractJson($raw);

        return [
            'score'             => max(0, min(100, (float) ($json['score'] ?? 50))),
            'feedback'          => (string) ($json['feedback'] ?? 'Tidak ada feedback.'),
            'rubric_breakdown'  => [
                'relevansi'          => max(0, min(100, (float) ($json['rubric_breakdown']['relevansi'] ?? 50))),
                'kelengkapan'        => max(0, min(100, (float) ($json['rubric_breakdown']['kelengkapan'] ?? 50))),
                'struktur_logika'    => max(0, min(100, (float) ($json['rubric_breakdown']['struktur_logika'] ?? 50))),
                'tata_bahasa'        => max(0, min(100, (float) ($json['rubric_breakdown']['tata_bahasa'] ?? 50))),
                'kedalaman_analisis' => max(0, min(100, (float) ($json['rubric_breakdown']['kedalaman_analisis'] ?? 50))),
            ],
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
            'score'             => 50,
            'feedback'          => $raw,
            'rubric_breakdown'  => [
                'relevansi' => 50, 'kelengkapan' => 50,
                'struktur_logika' => 50, 'tata_bahasa' => 50, 'kedalaman_analisis' => 50,
            ],
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
