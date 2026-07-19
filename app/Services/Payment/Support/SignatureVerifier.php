<?php

namespace App\Services\Payment\Support;

use App\Models\Payment\PaymentProvider;
use App\Services\Payment\Exceptions\InvalidWebhookSignatureException;

class SignatureVerifier
{
    public function __construct(protected PaymentProvider $provider) {}

    /**
     * Generic signature verification. Configurable via provider.extra_config.signature:
     * - method: 'sha256' | 'sha512' | 'hmac_sha256' | 'hmac_sha512'
     * - fields: ordered list of payload keys to concatenate
     * - separator: string between fields (default empty)
     * - signature_field: where to find the signature (default 'signature')
     * - signature_header: header name (alternative to body field)
     */
    public function verify(array $headers, array $payload): void
    {
        $config = (array) ($this->provider->extra_config['signature'] ?? []);
        $method = $config['method'] ?? 'sha512';
        $fields = (array) ($config['fields'] ?? []);

        if (!$fields) {
            return;
        }

        $secret = $this->provider->webhook_secret ?? $this->provider->secret_key;
        if (!$secret) {
            throw new InvalidWebhookSignatureException('Webhook secret not configured');
        }

        $signatureField  = $config['signature_field'] ?? 'signature';
        $signatureHeader = $config['signature_header'] ?? null;
        $signature       = $payload[$signatureField] ?? null;

        if (!$signature && $signatureHeader) {
            $headerKey = strtolower($signatureHeader);
            $normalizedHeaders = array_change_key_case($headers, CASE_LOWER);
            $signature = $normalizedHeaders[$headerKey][0] ?? $normalizedHeaders[$headerKey] ?? null;
            if (is_array($signature)) {
                $signature = $signature[0];
            }
        }

        if (!$signature) {
            throw new InvalidWebhookSignatureException('Signature not present in payload or headers');
        }

        $separator = $config['separator'] ?? '';
        $concat    = '';
        foreach ($fields as $field) {
            $value = data_get($payload, $field, '');
            $concat .= ($concat ? $separator : '') . (is_scalar($value) ? (string) $value : json_encode($value));
        }

        $expected = match ($method) {
            'sha256'      => hash('sha256', $concat . $secret),
            'sha512'      => hash('sha512', $concat . $secret),
            'hmac_sha256' => hash_hmac('sha256', $concat, $secret),
            'hmac_sha512' => hash_hmac('sha512', $concat, $secret),
            default       => throw new InvalidWebhookSignatureException("Unknown signature method: {$method}"),
        };

        if (!hash_equals($expected, (string) $signature)) {
            throw new InvalidWebhookSignatureException('Signature mismatch');
        }
    }
}
