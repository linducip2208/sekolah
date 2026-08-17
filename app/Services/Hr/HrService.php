<?php

namespace App\Services\Hr;

use App\Models\Hr\EmploymentContract;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\OvertimeRecord;
use App\Models\Academic\Staff;

class HrService
{
    /* ==================== CONTRACTS ==================== */

    public function createContract(int $schoolId, array $data): EmploymentContract
    {
        return EmploymentContract::create(array_merge([
            'status' => 'active',
        ], $data, ['school_id' => $schoolId]));
    }

    public function terminateContract(EmploymentContract $contract): EmploymentContract
    {
        $contract->update(['status' => 'terminated']);
        return $contract->fresh();
    }

    public function expireContracts(int $schoolId): int
    {
        return EmploymentContract::where('school_id', $schoolId)
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', now()->toDateString())
            ->update(['status' => 'expired']);
    }

    /* ==================== LEAVE ==================== */

    public function requestLeave(int $schoolId, int $staffId, array $data): LeaveRequest
    {
        $days = (int) $data['days'] ?? 1;

        return LeaveRequest::create(array_merge($data, [
            'school_id' => $schoolId,
            'staff_id'  => $staffId,
            'days'      => $days,
            'status'    => 'pending',
        ]));
    }

    public function approveLeave(LeaveRequest $leave, int $approverId): LeaveRequest
    {
        abort_if($leave->status !== 'pending', 422, 'Cuti sudah diproses.');
        $leave->update(['status' => 'approved', 'approved_by' => $approverId, 'approved_at' => now()]);
        return $leave->fresh();
    }

    public function rejectLeave(LeaveRequest $leave, int $approverId): LeaveRequest
    {
        abort_if($leave->status !== 'pending', 422, 'Cuti sudah diproses.');
        $leave->update(['status' => 'rejected', 'approved_by' => $approverId, 'approved_at' => now()]);
        return $leave->fresh();
    }

    /** Annual leave balance (default quota 12 days). */
    public function leaveBalance(int $schoolId, int $staffId, int $quota = 12): array
    {
        $used = LeaveRequest::where('school_id', $schoolId)
            ->where('staff_id', $staffId)
            ->where('type', 'annual')
            ->where('status', 'approved')
            ->sum('days');

        return ['quota' => $quota, 'used' => (int) $used, 'remaining' => max(0, $quota - (int) $used)];
    }

    /* ==================== OVERTIME ==================== */

    public function logOvertime(int $schoolId, int $staffId, array $data): OvertimeRecord
    {
        $hours = (float) ($data['hours'] ?? 0);
        $rate  = (int) ($data['rate_per_hour'] ?? 0);

        return OvertimeRecord::create([
            'school_id'     => $schoolId,
            'staff_id'      => $staffId,
            'date'          => $data['date'],
            'hours'         => $hours,
            'rate_per_hour' => $rate,
            'amount'        => (int) round($hours * $rate),
            'note'          => $data['note'] ?? null,
            'status'        => 'pending',
        ]);
    }

    public function approveOvertime(OvertimeRecord $record, int $approverId): OvertimeRecord
    {
        $record->update(['status' => 'approved', 'approved_by' => $approverId]);
        return $record->fresh();
    }
}
