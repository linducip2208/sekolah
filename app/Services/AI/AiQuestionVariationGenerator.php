<?php

namespace App\Services\AI;

use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Models\AI\AiUsageLog;
use App\Models\QuestionBank\QuestionBankItem;

class AiQuestionVariationGenerator
{
    public function __construct(protected AiAdapterFactory $factory) {}

    public function generate(
        int $schoolId,
        int $userId,
        int $questionId,
        int $variationCount,
        ?int $providerId = null,
        ?int $modelId = null,
    ): array {
        $original = QuestionBankItem::where('school_id', $schoolId)->findOrFail($questionId);

        $model    = $this->resolveModel($schoolId, $modelId);
        $provider = $providerId
            ? AiProvider::where('school_id', $schoolId)->where('id', $providerId)->where('is_active', true)->firstOrFail()
            : $model->provider;

        if (!$provider || !$provider->is_active) {
            throw new \RuntimeException('AI provider tidak aktif.');
        }

        $adapter  = $this->factory->for($provider, $model);
        $messages = $this->buildPrompt($original, $variationCount);

        $start = microtime(true);
        $result = $error = null;

        try {
            $result = $adapter->chat($messages, ['temperature' => 0.9, 'max_tokens' => 4096]);
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
                'feature_key'    => 'question_variation',
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
            'original'           => $original,
            'ai_provider_id'     => $provider->id,
            'ai_model_id'        => $model->id,
            'tokens_used'        => ($result['input_tokens'] ?? 0) + ($result['output_tokens'] ?? 0),
            'processing_time_ms' => $latencyMs,
        ];
    }

    public function generateAndSave(
        int $schoolId,
        int $userId,
        int $questionId,
        int $variationCount,
        ?int $providerId = null,
        ?int $modelId = null,
    ): array {
        $result   = $this->generate($schoolId, $userId, $questionId, $variationCount, $providerId, $modelId);
        $original = $result['original'];
        $parsed   = $result['parsed'];
        $variations = $parsed['variations'] ?? [];
        $saved = [];

        $maxVersion = QuestionBankItem::where('school_id', $schoolId)
            ->where('parent_id', $original->id)
            ->max('version') ?? $original->version;

        foreach ($variations as $v) {
            $maxVersion++;
            $item = QuestionBankItem::create([
                'school_id'                => $schoolId,
                'subject_id'               => $original->subject_id,
                'question_bank_category_id' => $original->question_bank_category_id,
                'author_id'                => $userId,
                'question_html'            => $v['question'] ?? '',
                'type'                     => $original->type,
                'question_type'            => $original->question_type,
                'options'                  => $v['options'] ?? $original->options,
                'answer_key'               => $v['answer_key'] ?? $original->answer_key,
                'explanation_html'         => $v['explanation'] ?? $original->explanation_html,
                'difficulty'               => $original->difficulty,
                'cognitive_level'          => $original->cognitive_level,
                'tags'                     => $original->tags,
                'version'                  => $maxVersion,
                'parent_id'                => $original->id,
                'status'                   => 'draft',
                'is_published'             => false,
            ]);
            $saved[] = $item;
        }

        return ['items' => $saved, 'ai' => $result];
    }

    protected function buildPrompt(QuestionBankItem $original, int $count): array
    {
        $questionData = json_encode([
            'question'       => $original->question_html,
            'type'           => $original->type,
            'options'        => $original->options,
            'answer_key'     => $original->answer_key,
            'difficulty'     => $original->difficulty,
            'cognitive_level'=> $original->cognitive_level,
        ], JSON_UNESCAPED_UNICODE);

        $system = <<<'PROMPT'
Anda adalah ahli pembuat soal ujian di Indonesia.
Buatkan variasi soal dengan kata-kata berbeda namun menguji konsep yang SAMA.
Jawaban benar harus tetap SAMA. Hanya wording yang berubah.
Response HARUS dalam format JSON:
{
  "variations": [
    {
      "question": "Variasi 1 dengan wording berbeda...",
      "options": [
        {"text": "A. ...", "is_correct": true},
        {"text": "B. ...", "is_correct": false},
        {"text": "C. ...", "is_correct": false},
        {"text": "D. ...", "is_correct": false}
      ],
      "answer_key": "A",
      "explanation": "Pembahasan..."
    }
  ]
}
Pastikan JSON valid. Pertahankan makna dan konsep yang sama.
PROMPT;

        $user = "Buatkan {$count} variasi dari soal berikut:\n\n{$questionData}";

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
