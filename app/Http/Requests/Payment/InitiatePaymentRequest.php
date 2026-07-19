<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'invoice_id'        => 'required|integer|exists:fee_invoices,id',
            'payment_method_id' => 'required|integer|exists:payment_methods,id',
            'idempotency_key'   => 'nullable|string|max:100',
        ];
    }
}
