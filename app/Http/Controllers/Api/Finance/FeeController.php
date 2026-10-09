<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Api\Concerns\ConvertsRupiah;
use App\Http\Controllers\Controller;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeeStructure;
use App\Services\Finance\FeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    use ConvertsRupiah;

    public function __construct(private FeeService $service) {}

    public function structures(): JsonResponse
    {
        return response()->json(
            $this->inRupiah(FeeStructure::with('classRoom')->get())
        );
    }

    public function storeStructure(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'frequency'     => 'required|in:monthly,quarterly,annual,one-time',
            // Mobile contract: whole rupiah → stored as minor units.
            'amount'        => 'required|integer|min:1',
            'class_room_id' => 'nullable|integer|exists:class_rooms,id',
        ]);
        $validated['amount'] = $this->toCents($validated['amount']);
        $validated['school_id'] = auth()->user()->school_id;
        return response()->json(
            $this->inRupiah(FeeStructure::create($validated)), 201);
    }

    public function invoices(Request $request): JsonResponse
    {
        $user = $request->user();

        // Student: own invoices only (ignore query filters to prevent IDOR).
        $student = \App\Models\Academic\Student::where('user_id', $user->id)->first();
        if ($student) {
            $invoices = FeeInvoice::where('student_id', $student->id)
                ->with('feeStructure', 'payments')
                ->latest()
                ->get();
            return response()->json($this->inRupiah($invoices));
        }

        // Parent: own children only (empty when no linked children — never fall through).
        $childIds = $user->parentStudents()->pluck('students.id');
        if ($user->hasRole('parent') || $childIds->isNotEmpty()) {
            $invoices = FeeInvoice::whereIn('student_id', $childIds)
                ->with('feeStructure', 'payments')
                ->latest()
                ->get();
            return response()->json($this->inRupiah($invoices));
        }

        // Staff: require permission; filters allowed.
        abort_unless($user->hasRole('super_admin') || $user->can('fee.view'), 403, 'Tidak memiliki izin keuangan.');

        $invoices = FeeInvoice::when($request->student_id, fn($q) => $q->where('student_id', $request->student_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->with('feeStructure', 'payments')
            ->latest()
            ->paginate(50);
        return response()->json($this->inRupiah($invoices));
    }

    public function generateMonthly(Request $request): JsonResponse
    {
        $validated = $request->validate(['period' => 'required|date_format:Y-m']);
        $count = $this->service->generateMonthlyInvoices(auth()->user()->school_id, $validated['period']);
        return response()->json(['generated' => $count]);
    }

    public function recordPayment(Request $request, int $invoiceId): JsonResponse
    {
        abort_unless(
            $request->user()->hasRole('super_admin') || $request->user()->can('fee.payment'),
            403,
            'Tidak memiliki izin mencatat pembayaran.'
        );
        $validated = $request->validate([
            // Mobile contract: whole rupiah → stored as minor units.
            'amount'         => 'required|integer|min:1',
            'payment_method' => 'sometimes|in:cash,transfer,qris',
        ]);

        $invoice = $this->service->recordPayment($invoiceId, $this->toCents($validated['amount']), auth()->id(), $validated['payment_method'] ?? 'cash');
        return response()->json($this->inRupiah($invoice->load('payments')));
    }

    public function myInvoices(Request $request): JsonResponse
    {
        $student = \App\Models\Academic\Student::where('user_id', $request->user()->id)->first();
        if (!$student) {
            return response()->json([]);
        }
        $invoices = FeeInvoice::where('student_id', $student->id)
            ->with('feeStructure')
            ->latest()
            ->get();
        return response()->json($this->inRupiah($invoices));
    }
}
