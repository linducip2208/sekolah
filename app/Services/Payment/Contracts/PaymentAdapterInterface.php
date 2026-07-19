<?php

namespace App\Services\Payment\Contracts;

use App\Services\Payment\Support\PaymentTransactionContext;

interface PaymentAdapterInterface
{
    /**
     * Initiate a transaction with the gateway.
     *
     * Returns normalized array:
     * - external_id: string (gateway-side reference)
     * - redirect_url?: string
     * - va_number?: string
     * - va_bank_code?: string
     * - qr_string?: string
     * - deeplink_url?: string
     * - expired_at?: \DateTimeInterface
     * - raw_request: array
     * - raw_response: array
     */
    public function createTransaction(PaymentTransactionContext $ctx): array;

    /**
     * Verify the signature on an incoming webhook.
     *
     * @throws \App\Services\Payment\Exceptions\InvalidWebhookSignatureException
     */
    public function verifyWebhook(array $headers, string $rawBody): void;

    /**
     * Parse webhook payload into normalized event.
     *
     * Returns:
     * - external_id: string
     * - status: 'paid'|'expired'|'failed'|'cancelled'|'refunded'|'pending'
     * - paid_at?: \DateTimeInterface
     * - gateway_transaction_id?: string
     * - raw: array
     */
    public function parseWebhook(array $payload): array;

    /**
     * Optional reconciliation — query gateway for current status.
     * Implementations that don't support polling may throw UnsupportedOperationException.
     */
    public function fetchStatus(string $externalId): array;
}
