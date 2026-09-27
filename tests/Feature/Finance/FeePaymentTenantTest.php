<?php

use App\Models\Academic\Student;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeePayment;
use App\Models\Finance\FeeStructure;
use App\Models\School;
use App\Models\User;
use App\Services\Finance\FeeService;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->schoolA = School::factory()->create(['settings' => []]);
    $this->schoolB = School::factory()->create(['settings' => []]);

    $this->adminA = User::factory()->create(['school_id' => $this->schoolA->id, 'is_active' => true]);
    $this->adminA->assignRole('admin');

    $studentUserA = User::factory()->create(['school_id' => $this->schoolA->id]);
    $this->studentA = Student::create(['user_id' => $studentUserA->id, 'school_id' => $this->schoolA->id]);

    $studentUserB = User::factory()->create(['school_id' => $this->schoolB->id]);
    $this->studentB = Student::create(['user_id' => $studentUserB->id, 'school_id' => $this->schoolB->id]);

    $this->structureA = FeeStructure::create([
        'school_id' => $this->schoolA->id, 'name' => 'SPP', 'frequency' => 'monthly',
        'amount' => 10000000, 'is_active' => true,
    ]);
    $this->structureB = FeeStructure::create([
        'school_id' => $this->schoolB->id, 'name' => 'SPP', 'frequency' => 'monthly',
        'amount' => 10000000, 'is_active' => true,
    ]);
});

test('recordPayment stamps school_id from invoice', function () {
    Sanctum::actingAs($this->adminA);

    $invoice = FeeInvoice::create([
        'school_id' => $this->schoolA->id, 'student_id' => $this->studentA->id,
        'fee_structure_id' => $this->structureA->id, 'invoice_no' => 'INV-TENANT-1',
        'due_date' => today()->addDays(30), 'amount' => 10000000, 'status' => 'unpaid', 'period' => '2026-01',
    ]);

    app(FeeService::class)->recordPayment($invoice->id, 5000000, $this->adminA->id);

    $payment = FeePayment::withoutGlobalScopes()->where('fee_invoice_id', $invoice->id)->first();
    expect($payment->school_id)->toBe($this->schoolA->id);
});

test('admin cannot pay another school invoice (404)', function () {
    Sanctum::actingAs($this->adminA);

    $foreign = FeeInvoice::withoutGlobalScopes()->create([
        'school_id' => $this->schoolB->id, 'student_id' => $this->studentB->id,
        'fee_structure_id' => $this->structureB->id, 'invoice_no' => 'INV-TENANT-2',
        'due_date' => today()->addDays(30), 'amount' => 10000000, 'status' => 'unpaid', 'period' => '2026-01',
    ]);

    $response = $this->postJson("/api/v1/fee/invoices/{$foreign->id}/pay", [
        'amount' => 1000000, 'payment_method' => 'cash',
    ]);

    $response->assertNotFound();
    expect(FeePayment::withoutGlobalScopes()->where('fee_invoice_id', $foreign->id)->count())->toBe(0);
});

test('FeePayment global scope isolates schools', function () {
    $invA = FeeInvoice::withoutGlobalScopes()->create([
        'school_id' => $this->schoolA->id, 'student_id' => $this->studentA->id,
        'fee_structure_id' => $this->structureA->id, 'invoice_no' => 'INV-TENANT-3',
        'due_date' => today()->addDays(30), 'amount' => 10000000, 'status' => 'unpaid', 'period' => '2026-01',
    ]);
    $invB = FeeInvoice::withoutGlobalScopes()->create([
        'school_id' => $this->schoolB->id, 'student_id' => $this->studentB->id,
        'fee_structure_id' => $this->structureB->id, 'invoice_no' => 'INV-TENANT-4',
        'due_date' => today()->addDays(30), 'amount' => 10000000, 'status' => 'unpaid', 'period' => '2026-01',
    ]);

    FeePayment::withoutGlobalScopes()->create([
        'school_id' => $this->schoolA->id, 'fee_invoice_id' => $invA->id,
        'collected_by' => $this->adminA->id, 'amount' => 1000, 'payment_method' => 'cash',
        'payment_date' => today()->toDateString(),
    ]);
    FeePayment::withoutGlobalScopes()->create([
        'school_id' => $this->schoolB->id, 'fee_invoice_id' => $invB->id,
        'collected_by' => $this->adminA->id, 'amount' => 2000, 'payment_method' => 'cash',
        'payment_date' => today()->toDateString(),
    ]);

    Sanctum::actingAs($this->adminA);

    expect(FeePayment::count())->toBe(1)
        ->and(FeePayment::first()->school_id)->toBe($this->schoolA->id);
});
