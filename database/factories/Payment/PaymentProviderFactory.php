<?php

namespace Database\Factories\Payment;

use App\Models\Payment\PaymentProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentProviderFactory extends Factory
{
    protected $model = PaymentProvider::class;

    public function definition(): array
    {
        return [
            'name'       => 'Test Provider ' . Str::random(4),
            'slug'       => 'test-provider-' . Str::lower(Str::random(6)),
            'api_format' => fake()->randomElement([
                PaymentProvider::FORMAT_REDIRECT_CHECKOUT,
                PaymentProvider::FORMAT_VIRTUAL_ACCOUNT,
                PaymentProvider::FORMAT_QRIS_DYNAMIC,
                PaymentProvider::FORMAT_BANK_TRANSFER_MANUAL,
                PaymentProvider::FORMAT_CASH,
            ]),
            'base_url'   => 'https://api.example.com',
            'is_sandbox' => true,
            'is_active'  => true,
        ];
    }
}
