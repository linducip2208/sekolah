<?php

namespace App\Services\Payment\Support;

use App\Models\Finance\FeeInvoice;
use App\Models\Payment\PaymentMethod;
use App\Models\Payment\PaymentProvider;

class PaymentTransactionContext
{
    public function __construct(
        public readonly FeeInvoice $invoice,
        public readonly PaymentMethod $method,
        public readonly PaymentProvider $provider,
        public readonly int $amountCents,
        public readonly int $feeAmountCents,
        public readonly int $netAmountCents,
        public readonly array $customer,
        public readonly string $referenceNo,
        public readonly string $callbackUrl,
        public readonly string $webhookUrl,
        public readonly int $expiryMinutes,
        public readonly string $idempotencyKey,
    ) {}

    public function toArray(): array
    {
        return [
            'invoice_id'      => $this->invoice->id,
            'method_id'       => $this->method->id,
            'provider_id'     => $this->provider->id,
            'amount_cents'    => $this->amountCents,
            'fee_amount'      => $this->feeAmountCents,
            'net_amount'      => $this->netAmountCents,
            'customer'        => $this->customer,
            'reference_no'    => $this->referenceNo,
            'callback_url'    => $this->callbackUrl,
            'webhook_url'     => $this->webhookUrl,
            'expiry_minutes'  => $this->expiryMinutes,
            'idempotency_key' => $this->idempotencyKey,
        ];
    }
}
