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

    public function structures(): JsonResponse
    {
        return response()->json(PayrollStructure::all());
    }

    public function storeStructure(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:allowance,deduction',
            'calculation' => 'required|in:fixed,percentage',
            'value'       => 'required|integer|min:0',
        ]);
        $validated['school_id'] = auth()->user()->school_id;
        return response()->json(PayrollStructure::create($validated), 201);
    }

    public function generateSlip(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_id' => 'required|integer|exists:staffs,id',
            'month'    => 'required|date_format:Y-m',
        ]);

        $slip = $this->service->generateSlip($validated['staff_id'], $validated['month']);
        return response()->json($slip, 201);
    }

    public function slips(Request $request): JsonResponse
    {
        $slips = SalarySlip::when($request->month, fn($q) => $q->where('month', $request->month))
            ->with('staff')
            ->get();
        return response()->json($slips);
    }

    public function markPaid(Request $request, SalarySlip $salarySlip): JsonResponse
    {
        $salarySlip->update(['status' => 'paid', 'paid_on' => today()]);
        return response()->json($salarySlip->fresh());
    }
}
