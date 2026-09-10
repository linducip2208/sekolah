<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\PayrollStructure;
use App\Models\Finance\SalarySlip;
use App\Services\Finance\PayrollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct(private PayrollService $service) {}

    public function structures(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'payroll.view');

        return response()->json(PayrollStructure::all());
    }

    public function storeStructure(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'payroll.manage');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:allowance,deduction',
            'calculation' => 'required|in:fixed,percentage',
            'value' => 'required|integer|min:0',
        ]);
        $validated['school_id'] = auth()->user()->school_id;

        return response()->json(PayrollStructure::create($validated), 201);
    }

    public function generateSlip(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'payroll.manage');
        $validated = $request->validate([
            'staff_id' => 'required|integer|exists:staffs,id',
            'month' => 'required|date_format:Y-m',
        ]);

        $slip = $this->service->generateSlip($validated['staff_id'], $validated['month']);

        return response()->json($slip, 201);
    }

    public function slips(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'payroll.view');
        $slips = SalarySlip::when($request->month, fn ($q) => $q->where('month', $request->month))
            ->with('staff')
            ->get();

        return response()->json($slips);
    }

    public function markPaid(Request $request, SalarySlip $salarySlip): JsonResponse
    {
        $this->requirePermission($request, 'payroll.finalize');
        abort_unless((int) $salarySlip->school_id === (int) $request->user()->school_id, 404);

        return response()->json($this->service->markPaid($salarySlip, (int) $request->user()->id));
    }

    private function requirePermission(Request $request, string $permission): void
    {
        abort_unless($request->user()->hasRole('super_admin') || $request->user()->can($permission), 403, 'Tidak memiliki izin payroll.');
    }
}
