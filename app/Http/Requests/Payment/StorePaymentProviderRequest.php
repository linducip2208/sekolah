<?php

namespace App\Http\Requests\Payment;

use App\Models\Payment\PaymentProvider;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:200',
            'api_format'     => 'required|in:' . implode(',', PaymentProvider::FORMATS),
            'base_url'       => 'nullable|url|max:500',
            'callback_url'   => 'nullable|url|max:500',
            'api_key'        => 'nullable|string|max:500',
            'secret_key'     => 'nullable|string|max:500',
            'merchant_id'    => 'nullable|string|max:500',
            'webhook_secret' => 'nullable|string|max:500',
            'extra_config'   => 'nullable|array',
            'extra_headers'  => 'nullable|array',
            'is_sandbox'     => 'nullable|boolean',
            'is_active'      => 'nullable|boolean',
            'priority'       => 'nullable|integer|min:0|max:1000',
        ];
    }
}
