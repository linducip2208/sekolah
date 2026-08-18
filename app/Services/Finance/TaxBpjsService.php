<?php

namespace App\Services\Finance;

use App\Models\Academic\Staff;
use App\Models\Finance\BpjsConfig;
use App\Models\Finance\BpjsReport;
use App\Models\Finance\Pph21Bracket;
use App\Models\Finance\StaffTaxProfile;

class TaxBpjsService
{
    /** Calculate BPJS contributions for a staff member */
    public function calculateBpjs(int $schoolId, int $staffId, int $monthlySalary): array
    {
        $config  = BpjsConfig::forSchool($schoolId);
        $profile = StaffTaxProfile::where('school_id', $schoolId)->where('staff_id', $staffId)->first();

        if ($profile && !$profile->is_bpjs_active) {
            return $this->zeroBpjs();
        }

        $kesehatanBase = min($monthlySalary, (int) $config->kesehatan_salary_cap);
        $jpBase        = min($monthlySalary, (int) $config->jp_salary_cap);

        $kesehatanEmployee = (int) round($kesehatanBase * $config->kesehatan_employee_pct / 10000);
        $kesehatanEmployer = (int) round($kesehatanBase * $config->kesehatan_employer_pct / 10000);
        $jkk              = (int) round($monthlySalary * $config->jkk_pct / 10000);
        $jkm              = (int) round($monthlySalary * $config->jkm_pct / 10000);
        $jhtEmployee      = (int) round($monthlySalary * $config->jht_employee_pct / 10000);
        $jhtEmployer      = (int) round($monthlySalary * $config->jht_employer_pct / 10000);
        $jpEmployee       = (int) round($jpBase * $config->jp_employee_pct / 10000);
        $jpEmployer       = (int) round($jpBase * $config->jp_employer_pct / 10000);

        $totalEmployee = $kesehatanEmployee + $jhtEmployee + $jpEmployee;
        $totalEmployer = $kesehatanEmployer + $jkk + $jkm + $jhtEmployer + $jpEmployer;

        return compact(
            'kesehatanEmployee', 'kesehatanEmployer',
            'jkk', 'jkm',
            'jhtEmployee', 'jhtEmployer',
            'jpEmployee', 'jpEmployer',
            'totalEmployee', 'totalEmployer',
        );
    }

    /** Calculate PPh21 monthly (using annual progressive tax) */
    public function calculatePph21Monthly(int $schoolId, int $staffId, int $monthlySalary): int
    {
        $profile = StaffTaxProfile::where('school_id', $schoolId)->where('staff_id', $staffId)->first();

        if ($profile && !$profile->is_pph21_active) {
            return 0;
        }

        $annualSalary = $monthlySalary * 12;

        // Deduct BPJS employee component (annual)
        $bpjs = $this->calculateBpjs($schoolId, $staffId, $monthlySalary);
        $annualBpjsDeduction = $bpjs['totalEmployee'] * 12;

        $taxableIncome = max(0, $annualSalary - $annualBpjsDeduction);

        // Deduct PTKP
        $ptkpStatus = $profile->pTKP_status ?? 1;
        $ptkpValues = StaffTaxProfile::ptkpValues();
        $ptkp       = $ptkpValues[$ptkpStatus] ?? $ptkpValues[1];

        // Additional dependent deduction: Rp 54.000.000 per dependent (capped at 3)
        $dependents = min($profile->number_of_dependents ?? 0, 3);
        $additionalPtkp = $dependents * 540000000;

        $pkp = max(0, $taxableIncome - $ptkp - $additionalPtkp);

        // Progressive tax
        $annualTax = $this->calculateProgressiveTax($schoolId, $pkp);

        // Monthly PPh21 = annual / 12
        return (int) round($annualTax / 12);
    }

    /** Progressive tax calculation using brackets */
    private function calculateProgressiveTax(int $schoolId, int $pkp): int
    {
        $brackets = Pph21Bracket::where('school_id', $schoolId)
            ->orderBy('min_annual')
            ->get();

        if ($brackets->isEmpty()) {
            // Default Indonesian PPh21 2024 brackets (in cents)
            $brackets = collect([
                ['min_annual' => 0, 'max_annual' => 600000000, 'rate_pct' => 500],
                ['min_annual' => 600000000, 'max_annual' => 2500000000, 'rate_pct' => 1500],
                ['min_annual' => 2500000000, 'max_annual' => 5000000000, 'rate_pct' => 2500],
                ['min_annual' => 5000000000, 'max_annual' => null, 'rate_pct' => 3000],
            ]);
        }

        $tax = 0;
        foreach ($brackets as $b) {
            $min = (int) $b->min_annual;
            $max = $b->max_annual !== null ? (int) $b->max_annual : PHP_INT_MAX;
            $rate = (int) $b->rate_pct;

            if ($pkp <= $min) break;

            $taxableInBracket = min($pkp, $max) - $min;
            $tax += (int) round($taxableInBracket * $rate / 10000);
        }

        return $tax;
    }

    /** Store BPJS report for a month */
    public function storeBpjsReport(int $schoolId, string $month, int $staffId, int $monthlySalary): BpjsReport
    {
        $bpjs = $this->calculateBpjs($schoolId, $staffId, $monthlySalary);

        return BpjsReport::updateOrCreate(
            ['school_id' => $schoolId, 'month' => $month, 'staff_id' => $staffId],
            [
                'salary_base'         => $monthlySalary,
                'kesehatan_employee'  => $bpjs['kesehatanEmployee'],
                'kesehatan_employer'  => $bpjs['kesehatanEmployer'],
                'jkk'                 => $bpjs['jkk'],
                'jkm'                 => $bpjs['jkm'],
                'jht_employee'        => $bpjs['jhtEmployee'],
                'jht_employer'        => $bpjs['jhtEmployer'],
                'jp_employee'         => $bpjs['jpEmployee'],
                'jp_employer'         => $bpjs['jpEmployer'],
                'total_employee'      => $bpjs['totalEmployee'],
                'total_employer'      => $bpjs['totalEmployer'],
            ]
        );
    }

    private function zeroBpjs(): array
    {
        return [
            'kesehatanEmployee' => 0, 'kesehatanEmployer' => 0,
            'jkk' => 0, 'jkm' => 0,
            'jhtEmployee' => 0, 'jhtEmployer' => 0,
            'jpEmployee' => 0, 'jpEmployer' => 0,
            'totalEmployee' => 0, 'totalEmployer' => 0,
        ];
    }
}
