<?php

namespace Tests\Feature\Scholarship;

use App\Models\Academic\Student;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeeStructure;
use App\Models\Plan;
use App\Models\Scholarship\ScholarshipApplication;
use App\Models\Scholarship\ScholarshipGrant;
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
                'name' => 'Beasiswa Berprestasi',
                'source' => 'internal_school',
                'discount_type' => 'percentage',
                'discount_value' => 50,
                'eligibility_criteria' => ['min_avg_score' => 85],
                'open_date' => today()->toDateString(),
                'close_date' => today()->addMonth()->toDateString(),
            ]);

        $response->assertStatus(201);
        $this->assertEquals('percentage', $response->json('discount_type'));
    }

    public function test_grant_applies_discount_to_invoice(): void
    {
        $admin = $this->makeAdmin();
        $student = Student::factory()->create(['school_id' => $admin->school_id]);

        $program = ScholarshipProgram::create([
            'school_id' => $admin->school_id,
            'name' => 'Test Program',
            'source' => 'internal_school',
            'discount_type' => 'percentage',
            'discount_value' => 25,
            'eligibility_criteria' => [],
            'open_date' => today(),
            'close_date' => today()->addMonth(),
            'is_active' => true,
        ]);

        $structure = FeeStructure::create([
            'school_id' => $admin->school_id,
            'name' => 'SPP',
            'frequency' => 'monthly',
            'amount' => 500_000_00,
            'is_active' => true,
        ]);

        $invoice = FeeInvoice::create([
            'school_id' => $admin->school_id,
            'student_id' => $student->id,
            'fee_structure_id' => $structure->id,
            'invoice_no' => 'INV-TEST-001',
            'due_date' => today()->endOfMonth(),
            'amount' => 500_000_00,
            'status' => 'unpaid',
        ]);

        $app = ScholarshipApplication::create([
            'school_id' => $admin->school_id,
            'scholarship_program_id' => $program->id,
            'student_id' => $student->id,
            'status' => 'submitted',
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

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/scholarship/applications/{$app->id}/apply-to-invoice", [
                'invoice_id' => $invoice->id,
            ])
            ->assertStatus(200);

        $this->assertEquals(125_000_00, $invoice->refresh()->discount);
        $this->assertSame(1, ScholarshipGrant::where('school_id', $admin->school_id)
            ->where('scholarship_application_id', $app->id)
            ->where('fee_invoice_id', $invoice->id)
            ->count());
    }

    public function test_application_cannot_reference_foreign_program(): void
    {
        $admin = $this->makeAdmin();
        $foreignSchool = School::factory()->create(['plan_id' => Plan::factory()->create()->id]);
        $foreignProgram = ScholarshipProgram::create([
            'school_id' => $foreignSchool->id,
            'name' => 'Program Foreign',
            'source' => 'foundation',
            'discount_type' => 'full',
            'discount_value' => 0,
            'eligibility_criteria' => [],
            'open_date' => today(),
            'close_date' => today()->addMonth(),
            'is_active' => true,
        ]);
        $student = Student::factory()->create(['school_id' => $admin->school_id]);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/scholarship/applications', [
                'scholarship_program_id' => $foreignProgram->id,
                'student_id' => $student->id,
            ])
            ->assertNotFound();
    }

    public function test_scholarship_cannot_be_applied_to_another_students_invoice(): void
    {
        $admin = $this->makeAdmin();
        $student = Student::factory()->create(['school_id' => $admin->school_id]);
        $otherStudent = Student::factory()->create(['school_id' => $admin->school_id]);
        $program = ScholarshipProgram::create([
            'school_id' => $admin->school_id,
            'name' => 'Program Mismatch',
            'source' => 'internal_school',
            'discount_type' => 'fixed',
            'discount_value' => 100_000,
            'eligibility_criteria' => [],
            'open_date' => today(),
            'close_date' => today()->addMonth(),
            'is_active' => true,
        ]);
        $app = ScholarshipApplication::create([
            'school_id' => $admin->school_id,
            'scholarship_program_id' => $program->id,
            'student_id' => $student->id,
            'status' => 'granted',
        ]);
        $structure = FeeStructure::create([
            'school_id' => $admin->school_id,
            'name' => 'SPP mismatch',
            'frequency' => 'monthly',
            'amount' => 500_000,
            'is_active' => true,
        ]);
        $invoice = FeeInvoice::create([
            'school_id' => $admin->school_id,
            'student_id' => $otherStudent->id,
            'fee_structure_id' => $structure->id,
            'invoice_no' => 'INV-MISMATCH-001',
            'due_date' => today()->endOfMonth(),
            'amount' => 500_000,
            'status' => 'unpaid',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/scholarship/applications/{$app->id}/apply-to-invoice", [
                'invoice_id' => $invoice->id,
            ])
            ->assertStatus(422);
    }

    protected function makeAdmin(): User
    {
        $school = School::factory()->create(['plan_id' => Plan::factory()->create()->id]);
        $user = User::factory()->create(['school_id' => $school->id]);
        $user->assignRole('admin');

        return $user;
    }
}
