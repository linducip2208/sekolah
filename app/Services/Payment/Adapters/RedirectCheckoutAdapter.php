<?php

namespace App\Services\Payment\Adapters;

use App\Services\Payment\Support\PaymentTransactionContext;

/**
 * Generic redirect-based checkout. Provider returns a URL the user is redirected to.
 *
 * Behaviour is configured via provider.extra_config:
 * - endpoint: path appended to base_url (default '/transactions')
 * - amount_unit: 'cents' or 'units' (default 'units' — most gateways)
 * - request_template: array template with {{ placeholders }} for payload
 * - response_paths: { external_id, redirect_url } JSON paths in response
 *
 * No vendor name appears in this class.
 */
class RedirectCheckoutAdapter extends BaseFormatAdapter
{
    public function createTransaction(PaymentTransactionContext $ctx): array
    {
        $cfg = (array) ($this->provider->extra_config ?? []);

        $endpoint   = $cfg['endpoint'] ?? '/transactions';
        $amountUnit = $cfg['amount_unit'] ?? 'units';
        $amount     = $amountUnit === 'cents' ? $ctx->amountCents : intdiv($ctx->amountCents, 100);

        $template = $cfg['request_template'] ?? [
            'transaction_details' => [
                'order_id'     => '{{ reference_no }}',
                'gross_amount' => '{{ amount }}',
            ],
            'customer_details' => [
                'first_name' => '{{ customer.name }}',
                'email'      => '{{ customer.email }}',
                'phone'      => '{{ customer.phone }}',
            ],
            'callbacks' => [
                'finish' => '{{ callback_url }}',
            ],
            'expiry' => [
                'unit'     => 'minutes',
                'duration' => '{{ expiry_minutes }}',
            ],
        ];

        $vars = [
            'reference_no'   => $ctx->referenceNo,
            'amount'         => $amount,
            'customer'       => $ctx->customer,
            'callback_url'   => $ctx->callbackUrl,
            'webhook_url'    => $ctx->webhookUrl,
            'expiry_minutes' => $ctx->expiryMinutes,
            'currency'       => 'IDR',
        ];

        $payload  = $this->fillTemplate($template, $vars);
        $response = $this->http->request('POST', $endpoint, $payload);

        $paths = $cfg['response_paths'] ?? [];

        return [
            'external_id'  => data_get($response, $paths['external_id'] ?? 'order_id', $ctx->referenceNo),
            'redirect_url' => data_get($response, $paths['redirect_url'] ?? 'redirect_url'),
            'expired_at'   => now()->addMinutes($ctx->expiryMinutes),
            'raw_request'  => $payload,
            'raw_response' => $response,
        ];
    }

    public function parseWebhook(array $payload): array
    {
        $cfg = (array) ($this->provider->extra_config ?? []);
        $map = (array) ($cfg['webhook_map'] ?? []);

        $statusValue   = data_get($payload, $map['status_field'] ?? 'transaction_status');
        $statusMapping = (array) ($map['status_values'] ?? [
            'paid'      => ['settlement', 'capture', 'success'],
            'pending'   => ['pending', 'authorize'],
            'expired'   => ['expire', 'expired'],
            'failed'    => ['deny', 'failure', 'failed'],
            'cancelled' => ['cancel', 'cancelled'],
            'refunded'  => ['refund', 'refunded'],
        ]);

        $normalized = 'pending';
        foreach ($statusMapping as $internal => $externals) {
            if (in_array($statusValue, (array) $externals, true)) {
                $normalized = $internal;
                break;
            }
        }

        return [
            'external_id'            => data_get($payload, $map['external_id_field'] ?? 'order_id'),
            'status'                 => $normalized,
            'gateway_transaction_id' => data_get($payload, $map['transaction_id_field'] ?? 'transaction_id'),
            'paid_at'                => $normalized === 'paid'
                ? (data_get($payload, $map['paid_at_field'] ?? 'transaction_time') ? now() : now())
                : null,
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
