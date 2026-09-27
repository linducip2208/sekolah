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

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            try {
                $result = $adapter->chat($messages, $options);
                $error = null;
                break;
            } catch (\Throwable $e) {
                $error = $e->getMessage();
                // Single retry for transient provider failures; then surface the error.
                if ($attempt === 1 && $this->isTransient($e)) {
                    usleep(500000);
                    continue;
                }
                $this->logUsage($schoolId, $userId, $model, $featureKey, $result, $start, $error);
                throw $e;
            }
        }

        $this->logUsage($schoolId, $userId, $model, $featureKey, $result, $start, $error);

        return $result;
    }

    protected function isTransient(\Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        foreach (['timeout', 'timed out', 'connection', 'temporarily', 'overloaded', 'rate limit', '429', '500', '502', '503', '504'] as $needle) {
            if (str_contains($message, $needle)) {
                return true;
            }
        }

        $code = (int) $e->getCode();
        return in_array($code, [0, 408, 429, 500, 502, 503, 504], true);
    }

    protected function logUsage(int $schoolId, int $userId, AiModel $model, string $featureKey, ?array $result, float $start, ?string $error): void
    {
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

    protected function estimateCost(AiModel $model, int $inputTokens, int $outputTokens): float
    {
        $inCost  = ($inputTokens / 1000) * (float) $model->input_price_per_1k;
        $outCost = ($outputTokens / 1000) * (float) $model->output_price_per_1k;
        return round($inCost + $outCost, 6);
    }
}
