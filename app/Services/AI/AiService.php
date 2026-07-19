<?php

namespace App\Services\AI;

use App\Models\AI\AiFeatureAssignment;
use App\Models\AI\AiModel;
use App\Models\AI\AiUsageLog;

class AiService
{
    public function __construct(protected AiAdapterFactory $factory) {}

    public function chatForFeature(int $schoolId, int $userId, string $featureKey, array $messages, array $options = []): array
    {
        $assignment = AiFeatureAssignment::where('school_id', $schoolId)
            ->where('feature_key', $featureKey)
            ->where('is_enabled', true)
            ->firstOrFail();

        $model = AiModel::where('school_id', $schoolId)
            ->where('id', $assignment->ai_model_id)
            ->where('is_active', true)
            ->firstOrFail();

        $provider = $model->provider;
        if (!$provider || !$provider->is_active) {
            throw new \RuntimeException('AI provider not active');
        }

        $adapter = $this->factory->for($provider, $model);

        // merge feature_config defaults
        $cfg = (array) ($assignment->feature_config ?? []);
        $options = array_merge($cfg, $options);

        // optionally prepend system message from feature config
        if (!empty($cfg['system_prompt'])) {
            array_unshift($messages, ['role' => 'system', 'content' => $cfg['system_prompt']]);
        }

        $start  = microtime(true);
        $result = null;
        $error  = null;
        try {
            $result = $adapter->chat($messages, $options);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            throw $e;
        } finally {
            $latencyMs = (int) round((microtime(true) - $start) * 1000);
            $cost      = $this->estimateCost($model, $result['input_tokens'] ?? 0, $result['output_tokens'] ?? 0);

            AiUsageLog::create([
                'school_id'      => $schoolId,
                'user_id'        => $userId,
                'ai_model_id'    => $model->id,
                'feature_key'    => $featureKey,
                'input_tokens'   => $result['input_tokens'] ?? 0,
                'output_tokens'  => $result['output_tokens'] ?? 0,
                'estimated_cost' => $cost,
                'latency_ms'     => $latencyMs,
                'success'        => $error === null,
                'error'          => $error,
            ]);
        }

        return $result;
    }

    protected function estimateCost(AiModel $model, int $inputTokens, int $outputTokens): float
    {
        $inCost  = ($inputTokens / 1000) * (float) $model->input_price_per_1k;
        $outCost = ($outputTokens / 1000) * (float) $model->output_price_per_1k;
        return round($inCost + $outCost, 6);
    }
}
