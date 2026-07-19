<?php

namespace App\Http\Requests\Donation;

use Illuminate\Foundation\Http\FormRequest;

class PublicDonateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'donor_name'   => 'required|string|max:200',
            'donor_email'  => 'required|email|max:200',
            'donor_phone'  => 'nullable|string|max:30',
            'npwp'         => 'nullable|regex:/^[\d\.\-]+$/|max:30',
            'is_anonymous' => 'nullable|boolean',
            'show_amount'  => 'nullable|boolean',
            'amount'       => 'required|integer|min:10000|max:1000000000',
            'message'      => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'Donasi minimum Rp 100 (10000 cents).',
        ];
    }
}
