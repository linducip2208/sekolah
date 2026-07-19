<?php

namespace App\Services\Finance;

use App\Models\Finance\PayrollStructure;
use App\Models\Finance\SalarySlip;

class PayrollService
{
    public function generateSlip(int $staffId, string $month): SalarySlip
    {
        $schoolId    = auth()->user()->school_id;
        $staff       = \App\Models\Academic\Staff::findOrFail($staffId);
        $basicSalary = $staff->basic_salary ?? 0;

        $structures = PayrollStructure::where('school_id', $schoolId)
            ->where('is_active', true)
            ->get();

        $allowances     = [];
        $deductions     = [];
        $totalAllowances = 0;
        $totalDeductions = 0;

        foreach ($structures as $structure) {
            $value = $structure->calculation === 'percentage'
                ? (int) ($basicSalary * $structure->value / 10000)
                : $structure->value;

            if ($structure->type === 'allowance') {
                $allowances[]    = ['name' => $structure->name, 'amount' => $value];
                $totalAllowances += $value;
            } else {
                $deductions[]    = ['name' => $structure->name, 'amount' => $value];
                $totalDeductions += $value;
            }
        }

        return SalarySlip::updateOrCreate(
            ['staff_id' => $staffId, 'month' => $month],
            [
                'school_id'          => $schoolId,
                'basic_salary'       => $basicSalary,
                'total_allowances'   => $totalAllowances,
                'total_deductions'   => $totalDeductions,
                'net_salary'         => $basicSalary + $totalAllowances - $totalDeductions,
                'allowances_detail'  => $allowances,
                'deductions_detail'  => $deductions,
                'status'             => 'draft',
            ]
        );
    }
}
