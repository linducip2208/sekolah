<?php

namespace App\Services\AI\Adapters;

use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Services\AI\Contracts\AiAdapterInterface;
use Illuminate\Support\Facades\Http;

/**
 * Generic adapter for OpenAI-compatible chat completions API.
 *
 * Covers (BUKAN hardcode): OpenAI, DeepSeek, Groq, Mistral, Together, Fireworks,
 * OpenRouter, xAI, Ollama, LM Studio, vLLM, Anyscale, Cerebras, etc.
 *
 * Endpoint: POST {base_url}/chat/completions
 */
class OpenAICompatibleAdapter implements AiAdapterInterface
{
    public function __construct(
        protected AiProvider $provider,
        protected AiModel $model,
    ) {}

    public function chat(array $messages, array $options = []): array
    {
        $payload = [
            'model'       => $this->model->model_name,
            'messages'    => $messages,
            'temperature' => $options['temperature'] ?? 0.7,
        ];
        if (isset($options['max_tokens'])) {
            $payload['max_tokens'] = (int) $options['max_tokens'];
        }
        if (!empty($options['json_mode'])) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $cfg     = (array) ($this->provider->extra_config ?? []);
        $endpoint = $cfg['chat_endpoint'] ?? '/chat/completions';

        $response = Http::timeout(60)
            ->withToken((string) $this->provider->api_key)
            ->withHeaders((array) ($this->provider->extra_headers ?? []))
            ->acceptJson()
            ->post(rtrim($this->provider->base_url, '/') . $endpoint, $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('AI gateway error: ' . $response->body());
        }

        $body = $response->json();

        return [
            'text'          => data_get($body, 'choices.0.message.content', ''),
            'input_tokens'  => (int) data_get($body, 'usage.prompt_tokens', 0),
            'output_tokens' => (int) data_get($body, 'usage.completion_tokens', 0),
            'raw'           => $body,
        ];
    }
}
