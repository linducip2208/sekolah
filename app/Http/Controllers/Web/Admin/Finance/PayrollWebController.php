<?php

namespace App\Http\Controllers\Web\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\Academic\Staff;
use App\Models\Finance\PayrollStructure;
use App\Models\Finance\SalarySlip;
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

        $created = 0;
        DB::transaction(function () use ($staffs, $structures, $data, $schoolId, &$created) {
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
                $created++;
            }
        });

        return back()->with('success', "$created slip baru dibuat untuk bulan {$data['month']}.");
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
}
