<?php

namespace App\Services\AI\Contracts;

interface AiAdapterInterface
{
    /**
     * Send a chat completion request.
     *
     * Input:
     * - messages: array of {role: 'system'|'user'|'assistant', content: string}
     * - max_tokens?, temperature?, json_mode?
     *
     * Returns:
     * - text: string (model output)
     * - input_tokens: int
     * - output_tokens: int
     * - raw: array (gateway response)
     */
    public function chat(array $messages, array $options = []): array;
}
