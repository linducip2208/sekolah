<?php

namespace App\Http\Resources\Payment;

use App\Models\Payment\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PaymentTransaction
 */
class PaymentTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'reference_no'        => $this->reference_no,
            'external_id'         => $this->external_id,
            'status'              => $this->status,
            'is_terminal'         => $this->isTerminal(),
            'is_expired'          => $this->isExpired(),
            'amount'              => $this->amount,
            'amount_formatted'    => 'Rp ' . number_format($this->amount / 100, 0, ',', '.'),
            'fee_amount'          => $this->fee_amount,
            'net_amount'          => $this->net_amount,
            'currency'            => $this->currency,
            'redirect_url'        => $this->redirect_url,
            'va_number'           => $this->va_number,
            'va_bank_code'        => $this->va_bank_code,
            'qr_string'           => $this->qr_string,
            'deeplink_url'        => $this->deeplink_url,
            'manual_instructions' => $this->raw_response['bank_accounts'] ?? null,
            'expired_at'          => $this->expired_at?->toIso8601String(),
            'paid_at'             => $this->paid_at?->toIso8601String(),
            'invoice'             => $this->whenLoaded('invoice', fn () => [
                'id'         => $this->invoice->id,
                'invoice_no' => $this->invoice->invoice_no,
                'period'     => $this->invoice->period,
            ]),
            'method'              => $this->whenLoaded('method', fn () => [
                'code'         => $this->method->code,
                'display_name' => $this->method->display_name,
                'instruction'  => $this->method->instruction_template,
            ]),
            'created_at'          => $this->created_at?->toIso8601String(),
        ];
    }
}
