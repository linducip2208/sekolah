<?php

use App\Models\Academic\Student;
use App\Models\Finance\BankStatement;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeePayment;
use App\Models\Finance\FeeStructure;
use App\Models\School;
use App\Models\User;
use App\Services\Finance\BankReconciliationService;

beforeEach(function () {
    $this->service = app(BankReconciliationService::class);

    $this->school = School::factory()->create();
    $user = User::factory()->create(['school_id' => $this->school->id]);
    $this->user = $user;
    $this->student = Student::create([
        'user_id' => $user->id, 'school_id' => $this->school->id, 'admission_no' => 'NIS-1',
    ]);

    $structure = FeeStructure::create([
        'school_id' => $this->school->id, 'name' => 'SPP', 'frequency' => 'monthly',
        'amount' => 150000, 'is_active' => true,
    ]);

    $this->invoice = FeeInvoice::create([
        'school_id' => $this->school->id, 'student_id' => $this->student->id,
        'fee_structure_id' => $structure->id, 'invoice_no' => 'INV-BR',
        'due_date' => '2026-12-31', 'amount' => 150000, 'paid_amount' => 150000,
        'status' => 'paid', 'period' => '2026-01',
    ]);

    $this->payment = FeePayment::create([
        'fee_invoice_id' => $this->invoice->id, 'collected_by' => $user->id,
        'amount' => 150000, 'payment_method' => 'bank_transfer', 'payment_date' => '2026-01-10',
    ]);
});

it('imports bank statement lines', function () {
    $count = $this->service->addLines($this->school->id, 'BCA 123', [
        ['transaction_date' => '2026-01-10', 'description' => 'SPP', 'amount' => 15000000],
        ['transaction_date' => '2026-01-11', 'description' => 'Operasional', 'amount' => -2000000],
    ]);

    expect($count)->toBe(2);
    expect(BankStatement::where('school_id', $this->school->id)->count())->toBe(2);
});

it('matches a statement line to a payment with equal amount', function () {
    $this->actingAs($this->user);
    $statement = BankStatement::create([
        'school_id' => $this->school->id, 'bank_account' => 'BCA',
        'transaction_date' => '2026-01-10', 'description' => 'SPP',
        'amount' => 150000, 'status' => 'unmatched',
    ]);

    $matched = $this->service->match($statement, $this->payment->id);

    expect($matched->status)->toBe('matched');
    expect($matched->fee_payment_id)->toBe($this->payment->id);
});

it('rejects a match when amounts differ', function () {
    $this->actingAs($this->user);
    $statement = BankStatement::create([
        'school_id' => $this->school->id, 'bank_account' => 'BCA',
        'transaction_date' => '2026-01-10', 'description' => 'SPP',
        'amount' => 99999, 'status' => 'unmatched',
    ]);

    $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    $this->service->match($statement, $this->payment->id);
});

it('computes reconciliation summary', function () {
    BankStatement::create([
        'school_id' => $this->school->id, 'transaction_date' => '2026-01-10',
        'amount' => 100000, 'status' => 'unmatched',
    ]);
    BankStatement::create([
        'school_id' => $this->school->id, 'transaction_date' => '2026-01-11',
        'amount' => 50000, 'status' => 'matched',
    ]);

    $summary = $this->service->summary($this->school->id);

    expect($summary['unmatched_count'])->toBe(1);
    expect($summary['matched_count'])->toBe(1);
    expect($summary['unmatched_credit'])->toBe(100000);
});
