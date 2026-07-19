<?php

namespace App\Services\AI\Adapters;

use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Services\AI\Contracts\AiAdapterInterface;
use Illuminate\Support\Facades\Http;

/**
 * Gemini-format adapter (Google generativelanguage API).
 * Format-based name. Works with Gemini API or any compatible drop-in.
 */
class GeminiFormatAdapter implements AiAdapterInterface
{
    public function __construct(
        protected AiProvider $provider,
        protected AiModel $model,
    ) {}

    public function chat(array $messages, array $options = []): array
    {
        $contents = [];
        $systemInstruction = null;
        foreach ($messages as $m) {
            $role = $m['role'] ?? 'user';
            if ($role === 'system') {
                $systemInstruction = ['parts' => [['text' => $m['content']]]];
                continue;
            }
            $contents[] = [
                'role' => $role === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $m['content']]],
            ];
        }

        $payload = [
            'contents'         => $contents,
            'generationConfig' => array_filter([
                'temperature'     => $options['temperature'] ?? 0.7,
                'maxOutputTokens' => $options['max_tokens'] ?? null,
            ], fn ($v) => $v !== null),
        ];
        if ($systemInstruction) {
            $payload['systemInstruction'] = $systemInstruction;
        }

        $cfg      = (array) ($this->provider->extra_config ?? []);
        $endpoint = $cfg['chat_endpoint'] ?? "/v1beta/models/{$this->model->model_name}:generateContent";

        $url = rtrim($this->provider->base_url, '/') . $endpoint
            . (str_contains($endpoint, '?') ? '&' : '?')
            . 'key=' . urlencode((string) $this->provider->api_key);

        $response = Http::timeout(60)
            ->withHeaders((array) ($this->provider->extra_headers ?? []))
            ->acceptJson()
            ->post($url, $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('AI gateway error: ' . $response->body());
        }

        $body = $response->json();
        $text = '';
        foreach ((array) data_get($body, 'candidates.0.content.parts', []) as $part) {
            if (isset($part['text'])) $text .= $part['text'];
        }

        return [
            'text'          => $text,
            'input_tokens'  => (int) data_get($body, 'usageMetadata.promptTokenCount', 0),
            'output_tokens' => (int) data_get($body, 'usageMetadata.candidatesTokenCount', 0),
            'raw'           => $body,
        ];
    }
}
