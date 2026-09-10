<?php

namespace App\Services\Finance;

use App\Models\Academic\Staff;
use App\Models\Finance\PayrollStructure;
use App\Models\Finance\SalarySlip;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public function __construct(
        private TaxBpjsService $taxBpjs,
        private AccountingService $accounting,
    ) {}

    public function generateSlip(int $staffId, string $month): SalarySlip
    {
        $schoolId = auth()->user()->school_id;
        $staff = Staff::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->findOrFail($staffId);
        $basicSalary = $staff->basic_salary ?? 0;

        $existing = SalarySlip::where('school_id', $schoolId)
            ->where('staff_id', $staffId)
            ->where('month', $month)
            ->first();
        abort_if($existing?->status === 'paid', 409, 'Slip gaji yang sudah final tidak dapat dibuat ulang.');

        $structures = PayrollStructure::where('school_id', $schoolId)
            ->where('is_active', true)
            ->get();

        $allowances = [];
        $deductions = [];
        $totalAllowances = 0;
        $totalDeductions = 0;

        foreach ($structures as $structure) {
            $value = $structure->calculation === 'percentage'
                ? (int) ($basicSalary * $structure->value / 10000)
                : $structure->value;

            if ($structure->type === 'allowance') {
                $allowances[] = ['name' => $structure->name, 'amount' => $value];
                $totalAllowances += $value;
            } else {
                $deductions[] = ['name' => $structure->name, 'amount' => $value];
                $totalDeductions += $value;
            }
        }

        // BPJS calculation
        $bpjs = $this->taxBpjs->calculateBpjs($schoolId, $staffId, $basicSalary);
        $bpjsEmployeeDeduction = $bpjs['totalEmployee'];

        // PPh21 monthly
        $pph21 = $this->taxBpjs->calculatePph21Monthly($schoolId, $staffId, $basicSalary);

        // Add BPJS employee portion to deductions
        if ($bpjsEmployeeDeduction > 0) {
            $deductions[] = ['name' => 'BPJS Kesehatan (员工)', 'amount' => $bpjs['kesehatanEmployee']];
            $deductions[] = ['name' => 'JHT (员工)', 'amount' => $bpjs['jhtEmployee']];
            $deductions[] = ['name' => 'JP (员工)', 'amount' => $bpjs['jpEmployee']];
            $totalDeductions += $bpjsEmployeeDeduction;
        }

        // Add PPh21 to deductions
        if ($pph21 > 0) {
            $deductions[] = ['name' => 'PPh21', 'amount' => $pph21];
            $totalDeductions += $pph21;
        }

        // The academic attendance table is student-scoped. Do not infer
        // payroll deductions from it; staff attendance must be recorded in
        // the HR attendance source before a school enables that policy.

        return SalarySlip::updateOrCreate(
            ['staff_id' => $staffId, 'month' => $month],
            [
                'school_id' => $schoolId,
                'basic_salary' => $basicSalary,
                'total_allowances' => $totalAllowances,
                'total_deductions' => $totalDeductions,
                'net_salary' => $basicSalary + $totalAllowances - $totalDeductions,
                'allowances_detail' => $allowances,
                'deductions_detail' => $deductions,
                'status' => 'draft',
            ]
        );
    }

    public function markPaid(SalarySlip $slip, int $actorId): SalarySlip
    {
        return DB::transaction(function () use ($slip, $actorId): SalarySlip {
            $locked = SalarySlip::where('school_id', $slip->school_id)
                ->whereKey($slip->id)
                ->lockForUpdate()
                ->firstOrFail();
            abort_if($locked->status === 'paid', 409, 'Slip gaji sudah final.');
            abort_unless($locked->net_salary >= 0, 422, 'Nilai gaji bersih tidak valid.');

            $locked->update(['status' => 'paid', 'paid_on' => today()]);
            $this->accounting->postPayroll(
                (int) $locked->school_id,
                (int) $locked->net_salary,
                'PAYROLL-'.$locked->id,
                $locked->paid_on?->toDateString(),
                $actorId,
            );

            return $locked->fresh();
        });
    }
}
