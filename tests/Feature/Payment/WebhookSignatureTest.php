<?php

namespace Tests\Feature\Payment;

use App\Models\Payment\PaymentProvider;
use App\Models\Plan;
use App\Models\School;
use Tests\TestCase;

class WebhookSignatureTest extends TestCase
{
    public function test_webhook_with_invalid_signature_rejected(): void
    {
        $school = School::factory()->create(['plan_id' => Plan::factory()->create()->id]);

        $provider = PaymentProvider::create([
            'school_id'   => $school->id,
            'name'        => 'Test',
            'slug'        => 'test-provider',
            'api_format'  => 'redirect_checkout',
            'base_url'    => 'https://api.example.com',
            'extra_config' => [
                'signature' => [
                    'method'           => 'sha512',
                    'fields'           => ['order_id', 'status_code', 'gross_amount'],
                    'signature_field'  => 'signature_key',
                ],
            ],
            'is_active'   => true,
        ]);
        $provider->webhook_secret = 'real-secret';
        $provider->save();

        $payload = [
            'order_id'      => 'PAY-1-1-ABC',
            'status_code'   => '200',
            'gross_amount'  => '100000.00',
            'transaction_status' => 'settlement',
            'signature_key' => 'WRONG_SIGNATURE',
        ];

        $response = $this->postJson("/api/v1/payments/webhook/{$provider->slug}", $payload);
        $response->assertStatus(401);
        $response->assertJson(['ok' => false, 'reason' => 'signature']);
    }

    public function test_webhook_unknown_provider_404(): void
    {
        $response = $this->postJson('/api/v1/payments/webhook/nonexistent-slug', []);
        $response->assertStatus(404);
    }
}
