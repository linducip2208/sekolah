<?php

namespace App\Services\AI;

use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Models\AI\AiUsageLog;

class AiRubricGenerator
{
    public function __construct(protected AiAdapterFactory $factory) {}

    public function generate(
        int $schoolId,
        int $userId,
        string $assignmentTitle,
        array $criteriaList,
        int $maxScore,
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
        $messages = $this->buildPrompt($assignmentTitle, $criteriaList, $maxScore);

        $start = microtime(true);
        $result = $error = null;

        try {
            $result = $adapter->chat($messages, ['temperature' => 0.6, 'max_tokens' => 2048]);
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
                'feature_key'    => 'rubric_generator',
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

    protected function buildPrompt(string $title, array $criteria, int $maxScore): array
    {
        $criteriaJson = json_encode($criteria, JSON_UNESCAPED_UNICODE);
        $system = <<<'PROMPT'
Anda adalah ahli asesmen pendidikan Indonesia.
Buatkan rubrik penilaian (analytic rubric) untuk tugas/penilaian.
Response HARUS dalam format JSON:
{
  "title": "...",
  "criteria": [
    {
      "name": "...",
      "weight": 25,
      "levels": [
        {"score": 4, "label": "Sangat Baik", "description": "..."},
        {"score": 3, "label": "Baik", "description": "..."},
        {"score": 2, "label": "Cukup", "description": "..."},
        {"score": 1, "label": "Kurang", "description": "..."}
      ]
    }
  ]
}
Pastikan JSON valid. Bobot semua kriteria harus berjumlah 100.
PROMPT;

        $user = "Buatkan rubrik untuk:\n"
            . "- Judul: {$title}\n"
            . "- Skor Maksimum: {$maxScore}\n"
            . "- Kriteria: {$criteriaJson}\n\n"
            . "Buat rubrik analitik dengan 4 level skor per kriteria.";

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
