<?php

namespace App\Services\Payment\Adapters;

use App\Services\Payment\Support\PaymentTransactionContext;

class QrisDynamicAdapter extends BaseFormatAdapter
{
    public function createTransaction(PaymentTransactionContext $ctx): array
    {
        $cfg        = (array) ($this->provider->extra_config ?? []);
        $endpoint   = $cfg['endpoint'] ?? '/qr_codes';
        $amountUnit = $cfg['amount_unit'] ?? 'units';
        $amount     = $amountUnit === 'cents' ? $ctx->amountCents : intdiv($ctx->amountCents, 100);

        $template = $cfg['request_template'] ?? [
            'reference_id' => '{{ reference_no }}',
            'type'         => 'DYNAMIC',
            'currency'     => 'IDR',
            'amount'       => '{{ amount }}',
            'expires_at'   => '{{ expired_at }}',
        ];

        $vars = [
            'reference_no'   => $ctx->referenceNo,
            'amount'         => $amount,
            'expiry_minutes' => $ctx->expiryMinutes,
            'expired_at'     => now()->addMinutes($ctx->expiryMinutes)->toIso8601String(),
        ];

        $payload  = $this->fillTemplate($template, $vars);
        $response = $this->http->request('POST', $endpoint, $payload);

        $paths = $cfg['response_paths'] ?? [];

        return [
            'external_id'  => data_get($response, $paths['external_id'] ?? 'id', $ctx->referenceNo),
            'qr_string'    => data_get($response, $paths['qr_string'] ?? 'qr_string'),
            'expired_at'   => now()->addMinutes($ctx->expiryMinutes),
            'raw_request'  => $payload,
            'raw_response' => $response,
        ];
    }

    public function parseWebhook(array $payload): array
    {
        $cfg = (array) ($this->provider->extra_config ?? []);
        $map = (array) ($cfg['webhook_map'] ?? []);

        $statusValue = data_get($payload, $map['status_field'] ?? 'status');
        $paidValues  = (array) ($map['paid_values'] ?? ['SUCCEEDED', 'PAID', 'COMPLETED']);

        $normalized = match (true) {
            in_array($statusValue, $paidValues, true) => 'paid',
            in_array($statusValue, ['EXPIRED'], true) => 'expired',
            in_array($statusValue, ['FAILED'], true) => 'failed',
            default => 'pending',
        };

        return [
            'external_id'            => data_get($payload, $map['external_id_field'] ?? 'reference_id'),
            'status'                 => $normalized,
            'gateway_transaction_id' => data_get($payload, $map['transaction_id_field'] ?? 'id'),
            'paid_at'                => $normalized === 'paid' ? now() : null,
            'raw'                    => $payload,
        ];
    }

    protected function fillTemplate(mixed $node, array $vars): mixed
    {
        if (is_string($node)) {
            $rendered = $this->renderTemplate($node, $vars);
            return is_numeric($rendered) ? $rendered + 0 : $rendered;
        }
        if (is_array($node)) {
            $out = [];
            foreach ($node as $k => $v) {
                $out[$k] = $this->fillTemplate($v, $vars);
            }
            return $out;
        }
        return $node;
    }
}
