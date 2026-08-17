<?php

use App\Models\Academic\Staff;
use App\Models\Hr\EmploymentContract;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\OvertimeRecord;
use App\Models\School;
use App\Models\User;
use App\Services\Hr\HrService;

beforeEach(function () {
    $this->service = app(HrService::class);
    $this->school = School::factory()->create();
    $user = User::factory()->create(['school_id' => $this->school->id]);
    $this->staff = Staff::create([
        'user_id' => $user->id, 'school_id' => $this->school->id, 'employee_id' => 'EMP-1',
        'department' => 'Guru', 'designation' => 'Guru',
    ]);
});

it('creates and terminates a contract', function () {
    $contract = $this->service->createContract($this->school->id, [
        'staff_id' => $this->staff->id, 'type' => 'contract', 'start_date' => '2026-01-01', 'salary' => 5000000,
    ]);

    expect($contract->status)->toBe('active');

    $terminated = $this->service->terminateContract($contract);
    expect($terminated->status)->toBe('terminated');
});

it('requests and approves leave with balance tracking', function () {
    $this->service->requestLeave($this->school->id, $this->staff->id, [
        'type' => 'annual', 'start_date' => '2026-08-17', 'end_date' => '2026-08-18', 'days' => 2,
    ]);

    $leave = LeaveRequest::first();
    expect($leave->status)->toBe('pending');

    $approved = $this->service->approveLeave($leave, $this->staff->user_id);
    expect($approved->status)->toBe('approved');

    $balance = $this->service->leaveBalance($this->school->id, $this->staff->id);
    expect($balance['used'])->toBe(2);
    expect($balance['remaining'])->toBe(10);
});

it('logs overtime and computes amount', function () {
    $record = $this->service->logOvertime($this->school->id, $this->staff->id, [
        'date' => '2026-08-17', 'hours' => 3, 'rate_per_hour' => 50000,
    ]);

    expect($record->amount)->toBe(150000);
    expect($record->status)->toBe('pending');
});
