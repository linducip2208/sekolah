<?php

use App\Models\Academic\Student;
use App\Models\Automation\AutomationRule;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeeStructure;
use App\Models\School;
use App\Models\User;
use App\Services\Automation\AutomationService;

beforeEach(function () {
    $this->service = app(AutomationService::class);
    $this->school = School::factory()->create();
    $user = User::factory()->create(['school_id' => $this->school->id]);
    $this->user = $user;
    $this->student = Student::create([
        'user_id' => $user->id, 'school_id' => $this->school->id, 'admission_no' => 'NIS-1',
    ]);
    $structure = FeeStructure::create([
        'school_id' => $this->school->id, 'name' => 'SPP', 'frequency' => 'monthly',
        'amount' => 100000, 'is_active' => true,
    ]);
    $this->invoice = FeeInvoice::create([
        'school_id' => $this->school->id, 'student_id' => $this->student->id,
        'fee_structure_id' => $structure->id, 'invoice_no' => 'INV-AUT',
        'due_date' => now()->subDays(5)->toDateString(), 'amount' => 100000,
        'paid_amount' => 0, 'status' => 'overdue', 'period' => '2026-01',
    ]);
});

it('renders template placeholders', function () {
    expect($this->service->render('Tagihan {student} sebesar {amount}', ['student' => 'Budi', 'amount' => 100000]))
        ->toBe('Tagihan Budi sebesar 100000');
});

it('executes a notify rule and logs the action', function () {
    $rule = AutomationRule::create([
        'school_id' => $this->school->id, 'name' => 'Pengingat SPP',
        'trigger_type' => 'fee_overdue', 'action_type' => 'notify',
        'action_config' => ['title' => 'Tagihan {student}', 'body' => 'Segera bayar {amount}'],
        'is_active' => true,
    ]);

    $count = $this->service->run($this->school->id, 'fee_overdue', [
        ['user_id' => $this->user->id, 'student' => 'Budi', 'amount' => 100000],
    ]);

    expect($count)->toBe(1);
    $this->assertDatabaseHas('automation_logs', ['automation_rule_id' => $rule->id, 'status' => 'success']);
    $this->assertDatabaseHas('notifications_log', ['user_id' => $this->user->id, 'type' => 'fee_overdue']);
});

it('finds overdue invoice events', function () {
    $events = $this->service->feeOverdueEvents($this->school->id);

    expect($events)->toHaveCount(1);
    expect($events[0]['student'])->toBe($this->student->user?->name);
});

it('skips inactive rules', function () {
    AutomationRule::create([
        'school_id' => $this->school->id, 'name' => 'Nonaktif',
        'trigger_type' => 'fee_overdue', 'action_type' => 'notify',
        'action_config' => [], 'is_active' => false,
    ]);

    expect($this->service->run($this->school->id, 'fee_overdue', [['user_id' => $this->user->id, 'student' => 'Budi']]))->toBe(0);
});
