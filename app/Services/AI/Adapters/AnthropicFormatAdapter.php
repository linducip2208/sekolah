<?php

namespace App\Services\AI\Adapters;

use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Services\AI\Contracts\AiAdapterInterface;
use Illuminate\Support\Facades\Http;

/**
 * Anthropic-format adapter (POST /v1/messages with system+messages format).
 * Class is format-based, NOT vendor-named. Anthropic API or any compatible drop-in.
 */
class AnthropicFormatAdapter implements AiAdapterInterface
{
    public function __construct(
        protected AiProvider $provider,
        protected AiModel $model,
    ) {}

    public function chat(array $messages, array $options = []): array
    {
        $system = '';
        $msgs   = [];
        foreach ($messages as $m) {
            if (($m['role'] ?? '') === 'system') {
                $system .= ($system ? "\n" : '') . ($m['content'] ?? '');
            } else {
                $msgs[] = ['role' => $m['role'], 'content' => $m['content']];
            }
        }

        $payload = [
            'model'       => $this->model->model_name,
            'system'      => $system,
            'messages'    => $msgs,
            'max_tokens'  => (int) ($options['max_tokens'] ?? 1024),
            'temperature' => $options['temperature'] ?? 0.7,
        ];

        $cfg            = (array) ($this->provider->extra_config ?? []);
        $endpoint       = $cfg['chat_endpoint'] ?? '/v1/messages';
        $version        = $cfg['anthropic_version'] ?? '2023-06-01';

        $response = Http::timeout(60)
            ->withHeaders(array_merge(
                [
                    'x-api-key'         => (string) $this->provider->api_key,
                    'anthropic-version' => $version,
                ],
                (array) ($this->provider->extra_headers ?? []),
            ))
            ->acceptJson()
            ->post(rtrim($this->provider->base_url, '/') . $endpoint, $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('AI gateway error: ' . $response->body());
        }

        $body = $response->json();
        $text = '';
        foreach ((array) data_get($body, 'content', []) as $part) {
            if (($part['type'] ?? '') === 'text') $text .= $part['text'];
        }

        return [
            'text'          => $text,
            'input_tokens'  => (int) data_get($body, 'usage.input_tokens', 0),
            'output_tokens' => (int) data_get($body, 'usage.output_tokens', 0),
            'raw'           => $body,
        ];
    }
}
