<?php

namespace App\Services\Payment\Adapters;

use App\Services\Payment\Exceptions\UnsupportedOperationException;
use App\Services\Payment\Support\PaymentTransactionContext;

/**
 * Cash — recorded directly by accountant via FeeService::recordPayment.
 * This adapter exists for symmetry; PaymentService skips adapter call when method is cash.
 */
class CashAdapter extends BaseFormatAdapter
{
    public function createTransaction(PaymentTransactionContext $ctx): array
    {
        return [
            'external_id'  => $ctx->referenceNo,
            'expired_at'   => null,
            'raw_request'  => ['mode' => 'cash'],
            'raw_response' => ['note' => 'Cash payment — record at counter via FeeService'],
        ];
    }

    public function verifyWebhook(array $headers, string $rawBody): void
    {
        throw new UnsupportedOperationException('Cash has no webhook');
    }

    public function parseWebhook(array $payload): array
    {
        throw new UnsupportedOperationException('Cash has no webhook');
    }
}
