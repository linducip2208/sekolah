<?php

namespace Tests\Feature\Payment;

use App\Models\Academic\Student;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeeStructure;
use App\Models\Payment\PaymentMethod;
use App\Models\Payment\PaymentProvider;
use App\Models\Payment\PaymentTransaction;
use App\Models\Plan;
use App\Models\School;
use App\Models\User;
use Tests\TestCase;

class WebhookSignatureTest extends TestCase
{
    public function test_webhook_with_invalid_signature_rejected(): void
    {
        $school = School::factory()->create(['plan_id' => Plan::factory()->create()->id]);

        $provider = PaymentProvider::create([
            'school_id' => $school->id,
            'name' => 'Test',
            'slug' => 'test-provider',
            'api_format' => 'redirect_checkout',
            'base_url' => 'https://api.example.com',
            'extra_config' => [
                'signature' => [
                    'method' => 'sha512',
                    'fields' => ['order_id', 'status_code', 'gross_amount'],
                    'signature_field' => 'signature_key',
                ],
            ],
            'is_active' => true,
        ]);
        $provider->webhook_secret = 'real-secret';
        $provider->save();

        $payload = [
            'order_id' => 'PAY-1-1-ABC',
            'status_code' => '200',
            'gross_amount' => '100000.00',
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

    public function test_duplicate_signed_webhook_is_recorded_without_double_payment(): void
    {
        $school = School::factory()->create(['plan_id' => Plan::factory()->create()->id]);
        $admin = User::factory()->create(['school_id' => $school->id]);
        $admin->assignRole('admin');

        $provider = PaymentProvider::create([
            'school_id' => $school->id,
            'name' => 'Replay Test',
            'slug' => 'replay-test-'.uniqid(),
            'api_format' => 'redirect_checkout',
            'extra_config' => [
                'signature' => [
                    'method' => 'sha512',
                    'fields' => ['order_id', 'status_code', 'gross_amount'],
                    'signature_field' => 'signature_key',
                ],
            ],
            'is_active' => true,
        ]);
        $provider->webhook_secret = 'replay-secret';
        $provider->save();

        $method = PaymentMethod::create([
            'school_id' => $school->id,
            'payment_provider_id' => $provider->id,
            'code' => 'replay',
            'display_name' => 'Replay',
            'is_active' => true,
        ]);
        $structure = FeeStructure::create([
            'school_id' => $school->id,
            'name' => 'Replay Fee',
            'frequency' => 'monthly',
            'amount' => 100_000,
            'is_active' => true,
        ]);
        $student = Student::factory()->create(['school_id' => $school->id]);
        $invoice = FeeInvoice::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'fee_structure_id' => $structure->id,
            'invoice_no' => 'INV-REPLAY-'.uniqid(),
            'due_date' => today()->addDays(7),
            'amount' => 100_000,
            'status' => 'unpaid',
        ]);
        PaymentTransaction::create([
            'school_id' => $school->id,
            'fee_invoice_id' => $invoice->id,
            'payment_method_id' => $method->id,
            'payment_provider_id' => $provider->id,
            'initiated_by' => $admin->id,
            'reference_no' => 'REF-REPLAY-'.uniqid(),
            'external_id' => 'PAY-REPLAY-1',
            'amount' => 100_000,
            'net_amount' => 100_000,
            'status' => PaymentTransaction::STATUS_AWAITING_PAYMENT,
        ]);

        $payload = [
            'order_id' => 'PAY-REPLAY-1',
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'transaction_status' => 'settlement',
        ];
        $payload['signature_key'] = hash('sha512', 'PAY-REPLAY-1200100000.00replay-secret');

        $first = $this->postJson("/api/v1/payments/webhook/{$provider->slug}", $payload);
        $second = $this->postJson("/api/v1/payments/webhook/{$provider->slug}", $payload);

        $first->assertOk()->assertJson(['processed' => 'processed']);
        $second->assertOk()->assertJson(['processed' => 'duplicate']);
        $this->assertDatabaseCount('fee_payments', 1);
        $this->assertDatabaseHas('payment_webhook_logs', [
            'processing_status' => 'duplicate',
            'signature_status' => 'valid',
        ]);
    }
}
