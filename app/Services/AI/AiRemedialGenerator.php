<?php

namespace App\Services\AI;

use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Models\AI\AiUsageLog;
use App\Models\Academic\Student;

class AiRemedialGenerator
{
    public function __construct(protected AiAdapterFactory $factory) {}

    public function generate(
        int $schoolId,
        int $userId,
        int $studentId,
        string $subjectName,
        array $weakTopics,
        ?int $providerId = null,
        ?int $modelId = null,
    ): array {
        $student = Student::where('school_id', $schoolId)->with('user:id,name')->findOrFail($studentId);
        $model   = $this->resolveModel($schoolId, $modelId);
        $provider = $providerId
            ? AiProvider::where('school_id', $schoolId)->where('id', $providerId)->where('is_active', true)->firstOrFail()
            : $model->provider;

        if (!$provider || !$provider->is_active) {
            throw new \RuntimeException('AI provider tidak aktif.');
        }

        $adapter  = $this->factory->for($provider, $model);
        $messages = $this->buildPrompt($subjectName, $weakTopics, $student->user->name ?? 'Siswa');

        $start = microtime(true);
        $result = $error = null;

        try {
            $result = $adapter->chat($messages, ['temperature' => 0.7, 'max_tokens' => 4096]);
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
                'feature_key'    => 'remedial_enrichment',
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
            'ai_provider_id'     => $provider->id,
            'ai_model_id'        => $model->id,
            'tokens_used'        => ($result['input_tokens'] ?? 0) + ($result['output_tokens'] ?? 0),
            'processing_time_ms' => $latencyMs,
        ];
    }

    protected function buildPrompt(string $subject, array $weakTopics, string $studentName): array
    {
        $topicsJson = json_encode($weakTopics, JSON_UNESCAPED_UNICODE);
        $system = <<<'PROMPT'
Anda adalah guru remedial profesional Indonesia.
Buatkan paket latihan remedial yang dipersonalisasi untuk siswa yang lemah pada topik tertentu.
Response HARUS dalam format JSON:
{
  "title": "Paket Remedial - ...",
  "student_name": "...",
  "subject": "...",
  "weak_topics": ["topik 1", "topik 2"],
  "diagnostic_notes": "Analisis kelemahan siswa...",
  "remedial_plan": {
    "objectives": ["..."],
    "materials": ["..."],
    "estimated_time_minutes": 45
  },
  "exercises": [
    {
      "question": "...",
      "type": "multiple_choice",
      "options": [
        {"text": "A. ...", "is_correct": true},
        {"text": "B. ...", "is_correct": false},
        {"text": "C. ...", "is_correct": false},
        {"text": "D. ...", "is_correct": false}
      ],
      "answer_key": "A",
      "explanation": "Pembahasan...",
      "difficulty": "easy",
      "cognitive_level": "c4"
    }
  ],
  "enrichment": {
    "advanced_questions": [
      {"question": "...", "type": "essay", "answer_key": "...", "difficulty": "hard"}
    ],
    "resources": ["..."]
  }
}
Pastikan JSON valid. Latihan dimulai dari yang mudah ke sulit.
PROMPT;

        $user = "Buatkan paket remedial untuk:\n"
            . "- Nama Siswa: {$studentName}\n"
            . "- Mata Pelajaran: {$subject}\n"
            . "- Topik Lemah: {$topicsJson}\n\n"
            . "Buat 5-8 soal latihan (semua tipe) dari yang mudah ke sulit, "
            . "sertakan 2-3 soal pengayaan untuk siswa yang sudah menguasai.";

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
