<?php

use App\Models\Academic\Student;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeeStructure;
use App\Models\School;
use App\Models\User;
use App\Services\Finance\FeeDunningService;
use App\Services\Finance\FeeOverdueService;

beforeEach(function () {
    $this->school = School::factory()->create();
    $this->user = User::factory()->create(['school_id' => $this->school->id]);
    $this->studentUser = User::factory()->create(['school_id' => $this->school->id]);
    $this->student = Student::create(['school_id' => $this->school->id, 'user_id' => $this->studentUser->id, 'admission_no' => 'ADM-'.uniqid(), 'gender' => 'male']);
    $this->structure = FeeStructure::create(['school_id' => $this->school->id, 'name' => 'SPP', 'frequency' => 'monthly', 'amount' => 20000000, 'is_active' => true]);
});

it('marks past-due invoices overdue', function () {
    $invoice = FeeInvoice::create([
        'school_id' => $this->school->id, 'student_id' => $this->student->id,
        'fee_structure_id' => $this->structure->id, 'invoice_no' => 'INV-OD-1',
        'due_date' => today()->subDays(2)->toDateString(), 'amount' => 100000, 'paid_amount' => 0,
        'discount' => 0, 'status' => 'unpaid', 'period' => '2026-09',
    ]);

    $count = app(FeeOverdueService::class)->markOverdue($this->school->id);

    expect($count)->toBe(1)->and($invoice->fresh()->status)->toBe('overdue');
});

it('sends H-3 dunning once and skips paid invoices', function () {
    $wa = Mockery::mock(\App\Services\Communication\WhatsAppNotificationService::class);
    $wa->shouldReceive('sendToGuardian')->once()->andReturn(['ok' => true]);
    app()->instance(\App\Services\Communication\WhatsAppNotificationService::class, $wa);

    $invoice = FeeInvoice::create([
        'school_id' => $this->school->id, 'student_id' => $this->student->id,
        'fee_structure_id' => $this->structure->id, 'invoice_no' => 'INV-DUN-1',
        'due_date' => today()->addDays(3)->toDateString(), 'amount' => 200000, 'paid_amount' => 0,
        'discount' => 0, 'status' => 'unpaid', 'period' => '2026-10',
    ]);

    $first = app(FeeDunningService::class)->run($this->school->id);
    expect($first['sent'])->toBe(1);

    // Run ulang: sudah ada log H-3 -> skip, tidak kirim WA lagi
    $wa2 = Mockery::mock(\App\Services\Communication\WhatsAppNotificationService::class);
    $wa2->shouldReceive('sendToGuardian')->zeroOrMoreTimes()->andReturn(['ok' => true]);
    app()->instance(\App\Services\Communication\WhatsAppNotificationService::class, $wa2);
    $second = app(FeeDunningService::class)->run($this->school->id);
    expect($second['sent'])->toBe(0);
});
