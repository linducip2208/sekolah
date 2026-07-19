<?php

namespace App\Services\Payment\Adapters;

use App\Services\Payment\Exceptions\UnsupportedOperationException;
use App\Services\Payment\Support\PaymentTransactionContext;

/**
 * Bank Transfer Manual — school registers their own bank account.
 * Parent transfers manually and uploads proof. Admin verifies → mark paid.
 *
 * Provider extra_config:
 * - bank_accounts: array of { bank_name, account_number, account_holder }
 * - unique_amount_suffix: bool (append last 3 digits of invoice id to amount for matching)
 */
class BankTransferManualAdapter extends BaseFormatAdapter
{
    public function createTransaction(PaymentTransactionContext $ctx): array
    {
        $cfg = (array) ($this->provider->extra_config ?? []);

        $accounts = (array) ($cfg['bank_accounts'] ?? []);

        return [
            'external_id'  => $ctx->referenceNo,
            'expired_at'   => now()->addMinutes($ctx->expiryMinutes),
            'raw_request'  => ['mode' => 'manual'],
            'raw_response' => [
                'bank_accounts'   => $accounts,
                'unique_suffix'   => (bool) ($cfg['unique_amount_suffix'] ?? false),
                'instructions'    => $cfg['instructions'] ?? null,
                'reference_no'    => $ctx->referenceNo,
            ],
        ];
    }

    public function verifyWebhook(array $headers, string $rawBody): void
    {
        throw new UnsupportedOperationException('Manual bank transfer has no webhook');
    }

    public function parseWebhook(array $payload): array
    {
        throw new UnsupportedOperationException('Manual bank transfer has no webhook');
    }
}
