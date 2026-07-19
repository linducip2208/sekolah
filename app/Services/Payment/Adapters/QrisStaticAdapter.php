<?php

namespace App\Services\Payment\Adapters;

use App\Services\Payment\Exceptions\UnsupportedOperationException;
use App\Services\Payment\Support\PaymentTransactionContext;

/**
 * Static QRIS — school uploaded a single fixed QR. No API calls.
 * Payment confirmation handled by manual admin verification or via NMID-based
 * webhook from the bank if configured.
 */
class QrisStaticAdapter extends BaseFormatAdapter
{
    public function createTransaction(PaymentTransactionContext $ctx): array
    {
        $cfg = (array) ($this->provider->extra_config ?? []);

        $qrString = $cfg['static_qr_string'] ?? null;
        if (!$qrString) {
            throw new UnsupportedOperationException('Static QR string not configured (extra_config.static_qr_string)');
        }

        return [
            'external_id'  => $ctx->referenceNo,
            'qr_string'    => $qrString,
            'expired_at'   => now()->addMinutes($ctx->expiryMinutes),
            'raw_request'  => ['mode' => 'static'],
            'raw_response' => ['note' => 'Static QRIS — verification manual or via bank webhook'],
        ];
    }

    public function verifyWebhook(array $headers, string $rawBody): void
    {
        if (!empty($this->provider->extra_config['signature'] ?? null)) {
            parent::verifyWebhook($headers, $rawBody);
        }
    }

    public function parseWebhook(array $payload): array
    {
        $cfg = (array) ($this->provider->extra_config ?? []);
        $map = (array) ($cfg['webhook_map'] ?? []);

        return [
            'external_id'            => data_get($payload, $map['external_id_field'] ?? 'reference_id'),
            'status'                 => 'paid',
            'gateway_transaction_id' => data_get($payload, $map['transaction_id_field'] ?? 'id'),
            'paid_at'                => now(),
            'raw'                    => $payload,
        ];
    }
}
