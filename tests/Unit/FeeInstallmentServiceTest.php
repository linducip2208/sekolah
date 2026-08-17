<?php

use App\Models\Academic\Student;
use App\Models\Finance\FeeInstallment;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeeStructure;
use App\Models\School;
use App\Models\User;
use App\Services\Finance\FeeInstallmentService;

beforeEach(function () {
    $this->service = app(FeeInstallmentService::class);

    $this->school = School::factory()->create();
    $user = User::factory()->create(['school_id' => $this->school->id]);
    $this->user = $user;
    $this->student = Student::create([
        'user_id' => $user->id, 'school_id' => $this->school->id, 'admission_no' => 'NIS-1',
    ]);

    $structure = FeeStructure::create([
        'school_id' => $this->school->id,
        'name' => 'SPP Bulanan',
        'frequency' => 'monthly',
        'amount' => 300000,
        'is_active' => true,
    ]);

    $this->invoice = FeeInvoice::create([
        'school_id' => $this->school->id,
        'student_id' => $this->student->id,
        'fee_structure_id' => $structure->id,
        'invoice_no' => 'INV-TEST',
        'due_date' => '2026-12-31',
        'amount' => 300000,
        'paid_amount' => 0,
        'status' => 'unpaid',
        'period' => '2026-01',
    ]);
});

it('splits an invoice into equal installments', function () {
    $created = $this->service->createSchedule($this->invoice, 3);

    expect($created)->toHaveCount(3);
    expect(array_sum(array_map(fn ($i) => $i->amount, $created)))->toBe(300000);

    $installments = FeeInstallment::where('fee_invoice_id', $this->invoice->id)->orderBy('installment_no')->get();
    expect($installments->pluck('installment_no')->all())->toBe([1, 2, 3]);
    expect($installments->first()->amount)->toBe(100000);
});

it('pays an installment and updates the invoice', function () {
    $this->actingAs($this->user);
    $this->service->createSchedule($this->invoice, 3);
    $installment = FeeInstallment::where('fee_invoice_id', $this->invoice->id)->first();

    $paid = $this->service->pay($installment, 100000, 'cash', 'REF-1');

    expect($paid->status)->toBe('paid');
    expect($this->invoice->fresh()->paid_amount)->toBe(100000);
    expect($this->invoice->fresh()->status)->toBe('partial');

    $this->assertDatabaseHas('fee_payments', ['fee_invoice_id' => $this->invoice->id, 'amount' => 100000]);
});

it('rejects a duplicate schedule', function () {
    $this->service->createSchedule($this->invoice, 2);

    $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    $this->service->createSchedule($this->invoice, 2);
});

it('marks past-due pending installments as overdue', function () {
    $this->service->createSchedule($this->invoice, 2, ['2026-01-01', '2026-02-01']);

    $count = $this->service->applyOverdue($this->school->id);

    expect($count)->toBe(2);
    expect(FeeInstallment::where('fee_invoice_id', $this->invoice->id)->where('status', 'overdue')->count())->toBe(2);
});
