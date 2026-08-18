<?php

namespace App\Http\Controllers\Web\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\Academic\Staff;
use App\Models\Finance\BpjsConfig;
use App\Models\Finance\BpjsReport;
use App\Models\Finance\Pph21Bracket;
use App\Models\Finance\PayrollStructure;
use App\Models\Finance\SalarySlip;
use App\Models\Finance\StaffTaxProfile;
use App\Services\Finance\TaxBpjsService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PayrollWebController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    /* ============== STRUCTURES (allowance/deduction) ============== */

    public function structures(): View
    {
        return view('school-admin.payroll.structures', [
            'structures' => PayrollStructure::where('school_id', $this->schoolId())->orderBy('type')->orderBy('name')->get(),
        ]);
    }

    public function storeStructure(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'type'        => 'required|in:allowance,deduction',
            'calculation' => 'required|in:fixed,percentage',
            'value'       => 'required|numeric|min:0',
        ]);

        PayrollStructure::create([
            'school_id'   => $this->schoolId(),
            'name'        => $data['name'],
            'type'        => $data['type'],
            'calculation' => $data['calculation'],
            'value'       => $data['calculation'] === 'fixed' ? (int) ($data['value'] * 100) : (int) $data['value'],
            'is_active'   => true,
        ]);

        return back()->with('success', 'Struktur ditambahkan.');
    }

    public function deleteStructure(PayrollStructure $structure): RedirectResponse
    {
        abort_unless($structure->school_id === $this->schoolId(), 403);
        $structure->delete();
        return back()->with('success', 'Struktur dihapus.');
    }

    /* ============== SALARY SLIPS ============== */

    public function slips(Request $request): View
    {
        $schoolId = $this->schoolId();
        $month = $request->month ?? now()->format('Y-m');

        $slips = SalarySlip::where('school_id', $schoolId)
            ->where('month', $month)
            ->with('staff.user:id,name')
            ->orderBy('id')
            ->get();

        return view('school-admin.payroll.slips', [
            'slips' => $slips,
            'month' => $month,
            'staffs' => Staff::where('school_id', $schoolId)->with('user:id,name')->orderBy('id')->get(),
        ]);
    }

    public function generateSlips(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'month' => 'required|regex:/^\d{4}-\d{2}$/',
        ]);

        $schoolId = $this->schoolId();
        $staffs = Staff::where('school_id', $schoolId)->whereNotNull('basic_salary')->get();
        $structures = PayrollStructure::where('school_id', $schoolId)->where('is_active', true)->get();
        $bpjsConfig = BpjsConfig::forSchool($schoolId);
        $taxService = app(TaxBpjsService::class);

        $created = 0;
        DB::transaction(function () use ($staffs, $structures, $data, $schoolId, &$created, $bpjsConfig, $taxService) {
            foreach ($staffs as $staff) {
                if (SalarySlip::where('school_id', $schoolId)->where('staff_id', $staff->id)->where('month', $data['month'])->exists()) continue;

                $basic = (int) $staff->basic_salary;
                $allowances = 0; $deductions = 0;
                $alDetail = []; $deDetail = [];

                foreach ($structures as $st) {
                    $amount = $st->calculation === 'fixed' ? (int) $st->value : (int) (($basic * $st->value) / 100);
                    if ($st->type === 'allowance') {
                        $allowances += $amount;
                        $alDetail[] = ['name' => $st->name, 'amount' => $amount];
                    } else {
                        $deductions += $amount;
                        $deDetail[] = ['name' => $st->name, 'amount' => $amount];
                    }
                }

                // BPJS
                $bpjs = $taxService->calculateBpjs($schoolId, $staff->id, $basic);
                if ($bpjs['totalEmployee'] > 0) {
                    $deDetail[] = ['name' => 'BPJS Kes', 'amount' => $bpjs['kesehatanEmployee']];
                    $deDetail[] = ['name' => 'JHT', 'amount' => $bpjs['jhtEmployee']];
                    $deDetail[] = ['name' => 'JP', 'amount' => $bpjs['jpEmployee']];
                    $deductions += $bpjs['totalEmployee'];
                }

                // PPh21
                $pph21 = $taxService->calculatePph21Monthly($schoolId, $staff->id, $basic);
                if ($pph21 > 0) {
                    $deDetail[] = ['name' => 'PPh21', 'amount' => $pph21];
                    $deductions += $pph21;
                }

                SalarySlip::create([
                    'school_id'         => $schoolId,
                    'staff_id'          => $staff->id,
                    'month'             => $data['month'],
                    'basic_salary'      => $basic,
                    'total_allowances'  => $allowances,
                    'total_deductions'  => $deductions,
                    'net_salary'        => $basic + $allowances - $deductions,
                    'allowances_detail' => $alDetail,
                    'deductions_detail' => $deDetail,
                    'status'            => 'draft',
                ]);

                // Store BPJS report
                $taxService->storeBpjsReport($schoolId, $data['month'], $staff->id, $basic);

                $created++;
            }
        });

        return back()->with('success', "$created slip baru dibuat untuk bulan {$data['month']} (termasuk BPJS & PPh21).");
    }

    public function paySlip(SalarySlip $slip): RedirectResponse
    {
        abort_unless($slip->school_id === $this->schoolId(), 403);
        $slip->update(['status' => 'paid', 'paid_on' => now()->toDateString()]);
        return back()->with('success', 'Slip ditandai sudah dibayar.');
    }

    public function deleteSlip(SalarySlip $slip): RedirectResponse
    {
        abort_unless($slip->school_id === $this->schoolId(), 403);
        if ($slip->status === 'paid') return back()->withErrors('Slip sudah dibayar, tidak bisa dihapus.');
        $slip->delete();
        return back()->with('success', 'Slip dihapus.');
    }

    /* ============== BPJS CONFIG ============== */

    public function bpjsConfig(): View
    {
        $schoolId = $this->schoolId();
        return view('school-admin.payroll.bpjs-config', [
            'config' => BpjsConfig::forSchool($schoolId),
        ]);
    }

    public function updateBpjsConfig(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kesehatan_employee_pct' => 'required|numeric|min:0|max:1000',
            'kesehatan_employer_pct' => 'required|numeric|min:0|max:1000',
            'kesehatan_salary_cap_rupiah' => 'required|numeric|min:0',
            'jkk_pct' => 'required|numeric|min:0|max:1000',
            'jkm_pct' => 'required|numeric|min:0|max:1000',
            'jht_employee_pct' => 'required|numeric|min:0|max:1000',
            'jht_employer_pct' => 'required|numeric|min:0|max:1000',
            'jp_employee_pct' => 'required|numeric|min:0|max:1000',
            'jp_employer_pct' => 'required|numeric|min:0|max:1000',
            'jp_salary_cap_rupiah' => 'required|numeric|min:0',
        ]);

        $schoolId = $this->schoolId();
        BpjsConfig::where('school_id', $schoolId)->update([
            'kesehatan_employee_pct' => (int) ($data['kesehatan_employee_pct'] * 100),
            'kesehatan_employer_pct' => (int) ($data['kesehatan_employer_pct'] * 100),
            'kesehatan_salary_cap'   => (int) ($data['kesehatan_salary_cap_rupiah'] * 100),
            'jkk_pct'                => (int) ($data['jkk_pct'] * 100),
            'jkm_pct'                => (int) ($data['jkm_pct'] * 100),
            'jht_employee_pct'       => (int) ($data['jht_employee_pct'] * 100),
            'jht_employer_pct'       => (int) ($data['jht_employer_pct'] * 100),
            'jp_employee_pct'        => (int) ($data['jp_employee_pct'] * 100),
            'jp_employer_pct'        => (int) ($data['jp_employer_pct'] * 100),
            'jp_salary_cap'          => (int) ($data['jp_salary_cap_rupiah'] * 100),
        ]);

        return back()->with('success', 'Konfigurasi BPJS diperbarui.');
    }

    /* ============== PPh21 BRACKETS ============== */

    public function pph21Brackets(): View
    {
        $schoolId = $this->schoolId();
        return view('school-admin.payroll.pph21-brackets', [
            'brackets' => Pph21Bracket::where('school_id', $schoolId)->orderBy('min_annual')->get(),
        ]);
    }

    public function storePph21Bracket(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'min_annual_rupiah' => 'required|numeric|min:0',
            'max_annual_rupiah' => 'nullable|numeric|min:0|gte:min_annual_rupiah',
            'rate_pct' => 'required|numeric|min:0|max:100',
        ]);

        Pph21Bracket::create([
            'school_id'  => $this->schoolId(),
            'min_annual' => (int) ($data['min_annual_rupiah'] * 100),
            'max_annual' => $data['max_annual_rupiah'] ? (int) ($data['max_annual_rupiah'] * 100) : null,
            'rate_pct'   => (int) ($data['rate_pct'] * 100),
        ]);

        return back()->with('success', 'Bracket PPh21 ditambahkan.');
    }

    public function deletePph21Bracket(Pph21Bracket $bracket): RedirectResponse
    {
        abort_unless($bracket->school_id === $this->schoolId(), 403);
        $bracket->delete();
        return back()->with('success', 'Bracket PPh21 dihapus.');
    }

    /* ============== STAFF TAX PROFILE ============== */

    public function staffTaxProfiles(): View
    {
        $schoolId = $this->schoolId();
        $staffs = Staff::where('school_id', $schoolId)->with('user:id,name')->orderBy('id')->get();
        $profiles = StaffTaxProfile::where('school_id', $schoolId)->get()->keyBy('staff_id');
        $ptkpLabels = [
            1 => 'TK/0', 2 => 'TK/1', 3 => 'K/0',
            4 => 'K/1', 5 => 'K/2', 6 => 'K/3',
        ];

        return view('school-admin.payroll.tax-profiles', compact('staffs', 'profiles', 'ptkpLabels'));
    }

    public function updateTaxProfile(Request $request, int $staffId): RedirectResponse
    {
        $data = $request->validate([
            'npwp' => 'nullable|string|max:20',
            'pTKP_status' => 'required|integer|min:1|max:6',
            'number_of_dependents' => 'required|integer|min:0|max:3',
            'is_bpjs_active' => 'boolean',
            'is_pph21_active' => 'boolean',
        ]);

        StaffTaxProfile::updateOrCreate(
            ['school_id' => $this->schoolId(), 'staff_id' => $staffId],
            [
                'npwp'                 => $data['npwp'] ?? null,
                'pTKP_status'          => $data['pTKP_status'],
                'number_of_dependents' => $data['number_of_dependents'],
                'is_bpjs_active'       => $data['is_bpjs_active'] ?? true,
                'is_pph21_active'      => $data['is_pph21_active'] ?? true,
            ]
        );

        return back()->with('success', 'Profil pajak diperbarui.');
    }
}
