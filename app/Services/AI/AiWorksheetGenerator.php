<?php

namespace App\Services\AI;

use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Models\AI\AiUsageLog;
use App\Models\QuestionBank\QuestionBankItem;

class AiWorksheetGenerator
{
    public function __construct(protected AiAdapterFactory $factory) {}

    public function generate(
        int $schoolId,
        int $userId,
        string $subjectName,
        string $topic,
        string $gradeLevel,
        int $questionCount,
        ?int $providerId = null,
        ?int $modelId = null,
    ): array {
        $model    = $this->resolveModel($schoolId, $modelId);
        $provider = $providerId
            ? AiProvider::where('school_id', $schoolId)->where('id', $providerId)->where('is_active', true)->firstOrFail()
            : $model->provider;

        if (!$provider || !$provider->is_active) {
            throw new \RuntimeException('AI provider tidak aktif.');
        }

        $adapter  = $this->factory->for($provider, $model);
        $messages = $this->buildPrompt($subjectName, $topic, $gradeLevel, $questionCount);

        $start = microtime(true);
        $result = $error = null;

        try {
            $result = $adapter->chat($messages, ['temperature' => 0.8, 'max_tokens' => 4096]);
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
                'feature_key'    => 'worksheet_generator',
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
            'ai_provider_id'     => $provider->id,
            'ai_model_id'        => $model->id,
            'tokens_used'        => ($result['input_tokens'] ?? 0) + ($result['output_tokens'] ?? 0),
            'processing_time_ms' => $latencyMs,
        ];
    }

    public function generateAndSave(
        int $schoolId,
        int $userId,
        array $data,
        ?int $providerId = null,
        ?int $modelId = null,
    ): array {
        $result = $this->generate(
            $schoolId, $userId,
            $data['subject_name'], $data['topic'], $data['grade_level'],
            (int) ($data['question_count'] ?? 10),
            $providerId, $modelId,
        );

        $parsed    = $result['parsed'];
        $questions = $parsed['questions'] ?? [];
        $saved     = [];

        foreach ($questions as $q) {
            $item = QuestionBankItem::create([
                'school_id'                => $schoolId,
                'subject_id'               => $data['subject_id'] ?? null,
                'question_bank_category_id' => $data['category_id'] ?? null,
                'author_id'                => $userId,
                'question_html'            => $q['question'] ?? '',
                'type'                     => 'multiple_choice',
                'question_type'            => $q['type'] ?? 'multiple_choice',
                'options'                  => $q['options'] ?? null,
                'answer_key'               => $q['answer_key'] ?? null,
                'explanation_html'         => $q['explanation'] ?? null,
                'difficulty'               => $q['difficulty'] ?? 'medium',
                'cognitive_level'          => $q['cognitive_level'] ?? 'c4',
                'tags'                     => $data['tags'] ?? [],
                'status'                   => 'draft',
                'is_published'             => false,
            ]);
            $saved[] = $item;
        }

        return ['items' => $saved, 'ai' => $result];
    }

    protected function buildPrompt(string $subject, string $topic, string $grade, int $count): array
    {
        $system = <<<'PROMPT'
Anda adalah guru profesional Indonesia pembuat soal.
Buatkan lembar kerja siswa (worksheet) dengan soal pilihan ganda dan essay.
Response HARUS dalam format JSON:
{
  "title": "Lembar Kerja - ...",
  "questions": [
    {
      "question": "Teks soal...",
      "type": "multiple_choice",
      "options": [
        {"text": "A. ...", "is_correct": true},
        {"text": "B. ...", "is_correct": false},
        {"text": "C. ...", "is_correct": false},
        {"text": "D. ...", "is_correct": false}
      ],
      "answer_key": "A",
      "explanation": "Pembahasan...",
      "difficulty": "medium",
      "cognitive_level": "c4"
    },
    {
      "question": "Teks soal essay...",
      "type": "essay",
      "options": null,
      "answer_key": "Kunci jawaban essay",
      "explanation": "Pembahasan...",
      "difficulty": "hard",
      "cognitive_level": "c6"
    }
  ]
}
Pastikan JSON valid. Variasikan tingkat kesulitan dan level kognitif.
PROMPT;

        $user = "Buatkan worksheet untuk:\n"
            . "- Mata Pelajaran: {$subject}\n"
            . "- Kelas: {$grade}\n"
            . "- Topik: {$topic}\n"
            . "- Jumlah Soal: {$count} (70% pilihan ganda, 30% essay)\n\n"
            . "Bervariasi dalam kesulitan (easy/medium/hard) dan level kognitif.";

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
