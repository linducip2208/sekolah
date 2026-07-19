<?php

namespace App\Services\AI;

use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Services\AI\Adapters\AnthropicFormatAdapter;
use App\Services\AI\Adapters\GeminiFormatAdapter;
use App\Services\AI\Adapters\OpenAICompatibleAdapter;
use App\Services\AI\Contracts\AiAdapterInterface;

class AiAdapterFactory
{
    public function for(AiProvider $provider, AiModel $model): AiAdapterInterface
    {
        return match ($provider->api_format) {
            AiProvider::FORMAT_OPENAI_COMPATIBLE => new OpenAICompatibleAdapter($provider, $model),
            AiProvider::FORMAT_ANTHROPIC         => new AnthropicFormatAdapter($provider, $model),
            AiProvider::FORMAT_GEMINI            => new GeminiFormatAdapter($provider, $model),
            default => throw new \InvalidArgumentException("Unsupported AI api_format: {$provider->api_format}"),
        };
    }
}
