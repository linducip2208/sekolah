<?php

namespace App\Services\Payment\Adapters;

use App\Services\Payment\Support\PaymentTransactionContext;

class EwalletDeeplinkAdapter extends BaseFormatAdapter
{
    public function createTransaction(PaymentTransactionContext $ctx): array
    {
        $cfg        = (array) ($this->provider->extra_config ?? []);
        $endpoint   = $cfg['endpoint'] ?? '/charges';
        $amountUnit = $cfg['amount_unit'] ?? 'units';
        $amount     = $amountUnit === 'cents' ? $ctx->amountCents : intdiv($ctx->amountCents, 100);

        $channelCode = $cfg['channel_code'] ?? strtoupper($ctx->method->code);

        $template = $cfg['request_template'] ?? [
            'reference_id'   => '{{ reference_no }}',
            'currency'       => 'IDR',
            'amount'         => '{{ amount }}',
            'channel_code'   => '{{ channel_code }}',
            'channel_properties' => [
                'success_redirect_url' => '{{ callback_url }}',
            ],
        ];

        $vars = [
            'reference_no'   => $ctx->referenceNo,
            'amount'         => $amount,
            'channel_code'   => $channelCode,
            'customer'       => $ctx->customer,
            'callback_url'   => $ctx->callbackUrl,
            'expiry_minutes' => $ctx->expiryMinutes,
        ];

        $payload  = $this->fillTemplate($template, $vars);
        $response = $this->http->request('POST', $endpoint, $payload);

        $paths = $cfg['response_paths'] ?? [];

        return [
            'external_id'  => data_get($response, $paths['external_id'] ?? 'id', $ctx->referenceNo),
            'deeplink_url' => data_get($response, $paths['deeplink_url'] ?? 'actions.mobile_deeplink_checkout_url'),
            'redirect_url' => data_get($response, $paths['redirect_url'] ?? 'actions.desktop_web_checkout_url'),
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
            in_array($statusValue, ['VOIDED', 'EXPIRED'], true) => 'expired',
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
