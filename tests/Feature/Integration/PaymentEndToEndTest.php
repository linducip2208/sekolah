<?php

namespace Tests\Feature\Integration;

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

class PaymentEndToEndTest extends TestCase
{
    public function test_full_cash_payment_flow(): void
    {
        [$admin, $invoice, $method] = $this->setupCashPayment();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/payments/initiate', [
                'invoice_id'        => $invoice->id,
                'payment_method_id' => $method->id,
                'idempotency_key'   => 'test-cash-001',
            ]);

        $response->assertStatus(201);
        $referenceNo = $response->json('reference_no');
        $this->assertNotEmpty($referenceNo);

        $tx = PaymentTransaction::where('reference_no', $referenceNo)->first();
        $this->assertNotNull($tx);
        $this->assertEquals($invoice->amount, $tx->amount);

        // Idempotency check — second call returns same transaction
        $response2 = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/payments/initiate', [
                'invoice_id'        => $invoice->id,
                'payment_method_id' => $method->id,
                'idempotency_key'   => 'test-cash-001',
            ]);

        $response2->assertStatus(201);
        $this->assertEquals($referenceNo, $response2->json('reference_no'));
    }

    public function test_payment_status_returns_403_for_other_school(): void
    {
        [$adminA, $invoice, $method] = $this->setupCashPayment();

        $response = $this->actingAs($adminA, 'sanctum')
            ->postJson('/api/v1/payments/initiate', [
                'invoice_id'        => $invoice->id,
                'payment_method_id' => $method->id,
            ]);
        $referenceNo = $response->json('reference_no');

        $schoolB = School::factory()->create(['plan_id' => Plan::factory()->create()->id]);
        $adminB = User::factory()->create(['school_id' => $schoolB->id]);
        $adminB->assignRole('admin');

        $response = $this->actingAs($adminB, 'sanctum')
            ->getJson("/api/v1/payments/{$referenceNo}");

        $response->assertStatus(404); // SchoolScope filters out
    }

    /** @return array{0:User,1:FeeInvoice,2:PaymentMethod} */
    protected function setupCashPayment(): array
    {
        $school = School::factory()->create(['plan_id' => Plan::factory()->create()->id]);
        $admin = User::factory()->create(['school_id' => $school->id]);
        $admin->assignRole('admin');

        $provider = PaymentProvider::create([
            'school_id'  => $school->id,
            'name'       => 'Cash',
            'slug'       => 'cash-' . uniqid(),
            'api_format' => PaymentProvider::FORMAT_CASH,
            'is_active'  => true,
        ]);

        $method = PaymentMethod::create([
            'school_id'           => $school->id,
            'payment_provider_id' => $provider->id,
            'code'                => 'cash',
            'display_name'        => 'Cash',
            'is_active'           => true,
        ]);

        $structure = FeeStructure::create([
            'school_id' => $school->id,
            'name'      => 'SPP',
            'frequency' => 'monthly',
            'amount'    => 500_000_00,
            'is_active' => true,
        ]);

        $student = Student::factory()->create(['school_id' => $school->id]);

        $invoice = FeeInvoice::create([
            'school_id'        => $school->id,
            'student_id'       => $student->id,
            'fee_structure_id' => $structure->id,
            'invoice_no'       => 'INV-TEST-' . uniqid(),
            'due_date'         => today()->endOfMonth(),
            'amount'           => 500_000_00,
            'status'           => 'unpaid',
        ]);

        return [$admin, $invoice, $method];
    }
}
