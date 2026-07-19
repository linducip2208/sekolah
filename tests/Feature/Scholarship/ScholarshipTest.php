<?php

namespace Tests\Feature\Scholarship;

use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeeStructure;
use App\Models\Plan;
use App\Models\Scholarship\ScholarshipApplication;
use App\Models\Scholarship\ScholarshipProgram;
use App\Models\School;
use App\Models\User;
use Tests\TestCase;

class ScholarshipTest extends TestCase
{
    public function test_admin_can_create_program(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/scholarship/programs', [
                'name'                 => 'Beasiswa Berprestasi',
                'source'               => 'internal_school',
                'discount_type'        => 'percentage',
                'discount_value'       => 50,
                'eligibility_criteria' => ['min_avg_score' => 85],
                'open_date'            => today()->toDateString(),
                'close_date'           => today()->addMonth()->toDateString(),
            ]);

        $response->assertStatus(201);
        $this->assertEquals('percentage', $response->json('discount_type'));
    }

    public function test_grant_applies_discount_to_invoice(): void
    {
        $admin = $this->makeAdmin();

        $program = ScholarshipProgram::create([
            'school_id'             => $admin->school_id,
            'name'                  => 'Test Program',
            'source'                => 'internal_school',
            'discount_type'         => 'percentage',
            'discount_value'        => 25,
            'eligibility_criteria'  => [],
            'open_date'              => today(),
            'close_date'             => today()->addMonth(),
            'is_active'              => true,
        ]);

        $structure = FeeStructure::create([
            'school_id' => $admin->school_id,
            'name'      => 'SPP',
            'frequency' => 'monthly',
            'amount'    => 500_000_00,
            'is_active' => true,
        ]);

        $invoice = FeeInvoice::create([
            'school_id'        => $admin->school_id,
            'student_id'       => 1,
            'fee_structure_id' => $structure->id,
            'invoice_no'       => 'INV-TEST-001',
            'due_date'         => today()->endOfMonth(),
            'amount'           => 500_000_00,
            'status'           => 'unpaid',
        ]);

        $app = ScholarshipApplication::create([
            'school_id'              => $admin->school_id,
            'scholarship_program_id' => $program->id,
            'student_id'             => 1,
            'status'                 => 'submitted',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/scholarship/applications/{$app->id}/grant", []);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/scholarship/applications/{$app->id}/apply-to-invoice", [
                'invoice_id' => $invoice->id,
            ]);

        $response->assertStatus(200);
        $invoice->refresh();
        $this->assertEquals(125_000_00, $invoice->discount); // 25% of 500_000_00
    }

    protected function makeAdmin(): User
    {
        $school = School::factory()->create(['plan_id' => Plan::factory()->create()->id]);
        $user = User::factory()->create(['school_id' => $school->id]);
        $user->assignRole('admin');
        return $user;
    }
}
