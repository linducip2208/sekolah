<?php

namespace App\Services\Payment\Adapters;

use App\Models\Payment\PaymentProvider;
use App\Services\Payment\Contracts\PaymentAdapterInterface;
use App\Services\Payment\Exceptions\UnsupportedOperationException;
use App\Services\Payment\Support\GatewayHttpClient;
use App\Services\Payment\Support\SignatureVerifier;

abstract class BaseFormatAdapter implements PaymentAdapterInterface
{
    public function __construct(
        protected PaymentProvider $provider,
        protected GatewayHttpClient $http,
        protected SignatureVerifier $verifier,
    ) {}

    public function verifyWebhook(array $headers, string $rawBody): void
    {
        $payload = json_decode($rawBody, true) ?: [];
        $this->verifier->verify($headers, $payload);
    }

    public function fetchStatus(string $externalId): array
    {
        throw new UnsupportedOperationException('fetchStatus not supported by ' . static::class);
    }

    /**
     * Render a string template with placeholders {{ field }} from the provider's extra_config.
     * Used by adapters that pull endpoint paths or field names from config.
     */
    protected function renderTemplate(string $template, array $vars): string
    {
        return preg_replace_callback('/\{\{\s*([\w.]+)\s*\}\}/', function ($m) use ($vars) {
            return (string) data_get($vars, $m[1], '');
        }, $template);
    }
}
