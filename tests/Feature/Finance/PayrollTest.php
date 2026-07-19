<?php

use App\Models\Academic\Staff;
use App\Models\Finance\PayrollStructure;
use App\Models\Finance\SalarySlip;
use App\Models\School;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->school = School::factory()->create(['settings' => []]);
    app()->instance('current_school', $this->school);

    $this->admin = User::factory()->create(['school_id' => $this->school->id, 'is_active' => true]);
    $this->admin->assignRole('admin');

    $staffUser   = User::factory()->create(['school_id' => $this->school->id]);
    $this->staff = Staff::create([
        'school_id'    => $this->school->id,
        'user_id'      => $staffUser->id,
        'basic_salary' => 500000000, // 5.000.000 cents
    ]);
});

test('admin can create payroll structure', function () {
    Sanctum::actingAs($this->admin);

    $response = $this->postJson('/api/v1/payroll/structures', [
        'name'        => 'Tunjangan Transport',
        'type'        => 'allowance',
        'calculation' => 'fixed',
        'value'       => 10000000,
    ]);

    $response->assertStatus(201)->assertJsonPath('name', 'Tunjangan Transport');
    expect(PayrollStructure::count())->toBe(1);
});

test('admin can generate salary slip', function () {
    Sanctum::actingAs($this->admin);

    PayrollStructure::create([
        'school_id'   => $this->school->id,
        'name'        => 'Potongan BPJS',
        'type'        => 'deduction',
        'calculation' => 'fixed',
        'value'       => 5000000,
        'is_active'   => true,
    ]);

    $response = $this->postJson('/api/v1/payroll/generate-slip', [
        'staff_id' => $this->staff->id,
        'month'    => '2025-01',
    ]);

    $response->assertStatus(201);
    $data = $response->json();
    expect($data['net_salary'])->toBe(500000000 - 5000000)
        ->and($data['status'])->toBe('draft');
});

test('admin can mark salary slip as paid', function () {
    Sanctum::actingAs($this->admin);

    $slip = SalarySlip::create([
        'school_id'        => $this->school->id,
        'staff_id'         => $this->staff->id,
        'month'            => '2025-01',
        'basic_salary'     => 500000000,
        'total_allowances' => 0,
        'total_deductions' => 0,
        'net_salary'       => 500000000,
        'status'           => 'draft',
    ]);

    $this->postJson("/api/v1/payroll/slips/{$slip->id}/mark-paid")->assertOk();
    $slip->refresh();
    expect($slip->status)->toBe('paid')
        ->and($slip->paid_on)->not->toBeNull();
});
