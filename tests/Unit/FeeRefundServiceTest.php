<?php

use App\Models\Academic\Student;
use App\Models\Finance\FeeInstallment;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeePayment;
use App\Models\Finance\FeeRefund;
use App\Models\Finance\FeeStructure;
use App\Models\School;
use App\Models\User;
use App\Services\Finance\FeeRefundService;

beforeEach(function () {
    $this->service = app(FeeRefundService::class);

    $this->school = School::factory()->create();
    $user = User::factory()->create(['school_id' => $this->school->id]);
    $this->user = $user;
    $this->student = Student::create([
        'user_id' => $user->id, 'school_id' => $this->school->id, 'admission_no' => 'NIS-1',
    ]);

    $structure = FeeStructure::create([
        'school_id' => $this->school->id, 'name' => 'SPP', 'frequency' => 'monthly',
        'amount' => 300000, 'is_active' => true,
    ]);

    $this->invoice = FeeInvoice::create([
        'school_id' => $this->school->id, 'student_id' => $this->student->id,
        'fee_structure_id' => $structure->id, 'invoice_no' => 'INV-REF',
        'due_date' => '2026-12-31', 'amount' => 300000, 'paid_amount' => 150000,
        'status' => 'partial', 'period' => '2026-01',
    ]);
});

it('records a refund and reduces invoice paid amount', function () {
    $this->actingAs($this->user);

    $refund = $this->service->refund($this->invoice, 50000, 'Kesalahan ganda');

    expect($refund->amount)->toBe(50000);
    expect($this->invoice->fresh()->paid_amount)->toBe(100000);
    expect($this->invoice->fresh()->status)->toBe('partial');

    $this->assertDatabaseHas('fee_refunds', ['fee_invoice_id' => $this->invoice->id, 'amount' => 50000]);
});

it('rejects refund exceeding paid amount', function () {
    $this->actingAs($this->user);

    $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    $this->service->refund($this->invoice, 999999, 'Terlalu besar');
});

it('applies daily late fee to overdue installments', function () {
    $installment = FeeInstallment::create([
        'school_id' => $this->school->id, 'fee_invoice_id' => $this->invoice->id,
        'installment_no' => 1, 'amount' => 100000, 'due_date' => now()->subDays(10)->toDateString(),
        'status' => 'overdue',
    ]);

    $count = $this->service->applyLateFee($this->school->id, 1000);

    expect($count)->toBe(1);
    expect($installment->fresh()->late_fee)->toBe(10000);
});
