<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Api\Concerns\ConvertsRupiah;
use App\Http\Controllers\Controller;
use App\Models\Finance\PayrollStructure;
use App\Models\Finance\SalarySlip;
use App\Services\Finance\PayrollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    use ConvertsRupiah;

    public function __construct(private PayrollService $service) {}

    public function structures(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'payroll.view');

        return response()->json($this->structuresInRupiah(PayrollStructure::all()));
    }

    public function storeStructure(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'payroll.manage');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:allowance,deduction',
            'calculation' => 'required|in:fixed,percentage',
            // Mobile contract: whole rupiah for fixed, plain percent otherwise.
            'value' => 'required|integer|min:0',
        ]);
        $validated['value'] = $validated['calculation'] === 'fixed'
            ? $this->toCents($validated['value'])
            : (int) $validated['value'];
        $validated['school_id'] = auth()->user()->school_id;

        return response()->json($this->structuresInRupiah([PayrollStructure::create($validated)])[0], 201);
    }

    public function generateSlip(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'payroll.manage');
        $validated = $request->validate([
            'staff_id' => 'required|integer|exists:staffs,id',
            'month' => 'required|date_format:Y-m',
        ]);

        $slip = $this->service->generateSlip($validated['staff_id'], $validated['month']);

        return response()->json($this->inRupiah($slip), 201);
    }

    public function slips(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'payroll.view');
        $slips = SalarySlip::when($request->month, fn ($q) => $q->where('month', $request->month))
            ->with('staff')
            ->get();

        return response()->json($this->inRupiah($slips));
    }

    public function markPaid(Request $request, SalarySlip $salarySlip): JsonResponse
    {
        $this->requirePermission($request, 'payroll.finalize');
        abort_unless((int) $salarySlip->school_id === (int) $request->user()->school_id, 404);

        return response()->json($this->inRupiah($this->service->markPaid($salarySlip, (int) $request->user()->id)));
    }

    /**
     * Payroll structures: `value` is money only when fixed.
     *
     * @param iterable $structures
     */
    private function structuresInRupiah(iterable $structures): array
    {
        $out = [];
        foreach ($structures as $s) {
            $row = $s instanceof \Illuminate\Contracts\Support\Arrayable ? $s->toArray() : (array) $s;
            if (($row['calculation'] ?? null) === 'fixed' && isset($row['value']) && is_numeric($row['value'])) {
                $row['value'] = intdiv((int) $row['value'], 100);
            }
            $out[] = $row;
        }

        return $out;
    }

    private function requirePermission(Request $request, string $permission): void
    {
        abort_unless($request->user()->hasRole('super_admin') || $request->user()->can($permission), 403, 'Tidak memiliki izin payroll.');
    }
}
