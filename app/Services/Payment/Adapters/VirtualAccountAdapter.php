<?php

namespace App\Services\Payment\Adapters;

use App\Services\Payment\Support\PaymentTransactionContext;

class VirtualAccountAdapter extends BaseFormatAdapter
{
    public function createTransaction(PaymentTransactionContext $ctx): array
    {
        $cfg        = (array) ($this->provider->extra_config ?? []);
        $endpoint   = $cfg['endpoint'] ?? '/charges';
        $amountUnit = $cfg['amount_unit'] ?? 'units';
        $amount     = $amountUnit === 'cents' ? $ctx->amountCents : intdiv($ctx->amountCents, 100);

        $bankCode = $cfg['bank_code'] ?? $ctx->method->code;

        $template = $cfg['request_template'] ?? [
            'external_id'   => '{{ reference_no }}',
            'bank_code'     => '{{ bank_code }}',
            'name'          => '{{ customer.name }}',
            'expected_amount' => '{{ amount }}',
            'is_closed'     => true,
            'expiration_date' => '{{ expired_at }}',
        ];

        $vars = [
            'reference_no'   => $ctx->referenceNo,
            'bank_code'      => $bankCode,
            'amount'         => $amount,
            'customer'       => $ctx->customer,
            'callback_url'   => $ctx->callbackUrl,
            'webhook_url'    => $ctx->webhookUrl,
            'expiry_minutes' => $ctx->expiryMinutes,
            'expired_at'     => now()->addMinutes($ctx->expiryMinutes)->toIso8601String(),
        ];

        $payload  = $this->fillTemplate($template, $vars);
        $response = $this->http->request('POST', $endpoint, $payload);

        $paths = $cfg['response_paths'] ?? [];

        return [
            'external_id'  => data_get($response, $paths['external_id'] ?? 'id', $ctx->referenceNo),
            'va_number'    => data_get($response, $paths['va_number'] ?? 'account_number'),
            'va_bank_code' => data_get($response, $paths['va_bank_code'] ?? 'bank_code', $bankCode),
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
        $paidValues  = (array) ($map['paid_values'] ?? ['PAID', 'COMPLETED', 'SETTLED']);

        $normalized = match (true) {
            in_array($statusValue, $paidValues, true) => 'paid',
            in_array($statusValue, ['EXPIRED'], true) => 'expired',
            in_array($statusValue, ['FAILED', 'FAILURE'], true) => 'failed',
            default => 'pending',
        };

        return [
            'external_id'            => data_get($payload, $map['external_id_field'] ?? 'external_id'),
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
