<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeeStructure;
use App\Services\Finance\FeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function __construct(private FeeService $service) {}

    public function structures(): JsonResponse
    {
        return response()->json(FeeStructure::with('classRoom')->get());
    }

    public function storeStructure(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'frequency'     => 'required|in:monthly,quarterly,annual,one-time',
            'amount'        => 'required|integer|min:1',
            'class_room_id' => 'nullable|integer|exists:class_rooms,id',
        ]);
        $validated['school_id'] = auth()->user()->school_id;
        return response()->json(FeeStructure::create($validated), 201);
    }

    public function invoices(Request $request): JsonResponse
    {
        $invoices = FeeInvoice::when($request->student_id, fn($q) => $q->where('student_id', $request->student_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->with('feeStructure', 'payments')
            ->latest()
            ->get();
        return response()->json($invoices);
    }

    public function generateMonthly(Request $request): JsonResponse
    {
        $validated = $request->validate(['period' => 'required|date_format:Y-m']);
        $count = $this->service->generateMonthlyInvoices(auth()->user()->school_id, $validated['period']);
        return response()->json(['generated' => $count]);
    }

    public function recordPayment(Request $request, int $invoiceId): JsonResponse
    {
        $validated = $request->validate([
            'amount'         => 'required|integer|min:1',
            'payment_method' => 'sometimes|in:cash,transfer,qris',
        ]);

        $invoice = $this->service->recordPayment($invoiceId, $validated['amount'], auth()->id(), $validated['payment_method'] ?? 'cash');
        return response()->json($invoice->load('payments'));
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
        return response()->json($invoices);
    }
}
