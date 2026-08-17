<?php

namespace App\Http\Controllers\Web\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Student;
use App\Models\Finance\FeeInstallment;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeePayment;
use App\Models\Finance\FeeRefund;
use App\Models\Finance\FeeStructure;
use App\Services\Finance\FeeInstallmentService;
use App\Services\Finance\FeeRefundService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FeeWebController extends Controller
{
    public function __construct(
        private FeeInstallmentService $installments,
        private FeeRefundService $refunds,
    ) {}
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    /* ============== FEE STRUCTURES ============== */

    public function structures(): View
    {
        $schoolId = $this->schoolId();
        return view('school-admin.finance.structures', [
            'structures' => FeeStructure::where('school_id', $schoolId)
                ->with('classRoom')->orderBy('name')->get(),
            'classes'    => ClassRoom::where('school_id', $schoolId)->orderBy('name')->get(),
        ]);
    }

    public function storeStructure(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:200',
            'frequency'     => 'required|in:monthly,semester,yearly,one_time',
            'amount_rupiah' => 'required|numeric|min:0',
            'class_room_id' => 'nullable|exists:class_rooms,id',
        ]);

        FeeStructure::create([
            'school_id'     => $this->schoolId(),
            'name'          => $data['name'],
            'frequency'     => $data['frequency'],
            'amount'        => (int) ($data['amount_rupiah'] * 100),
            'class_room_id' => $data['class_room_id'] ?? null,
            'is_active'     => true,
        ]);

        return back()->with('success', 'Fee structure ditambahkan.');
    }

    public function updateStructure(Request $request, FeeStructure $structure): RedirectResponse
    {
        $this->authorizeOwn($structure);
        $data = $request->validate([
            'name'          => 'required|string|max:200',
            'frequency'     => 'required|in:monthly,semester,yearly,one_time',
            'amount_rupiah' => 'required|numeric|min:0',
            'class_room_id' => 'nullable|exists:class_rooms,id',
            'is_active'     => 'nullable|boolean',
        ]);
        $structure->update([
            'name'          => $data['name'],
            'frequency'     => $data['frequency'],
            'amount'        => (int) ($data['amount_rupiah'] * 100),
            'class_room_id' => $data['class_room_id'] ?? null,
            'is_active'     => (bool) ($data['is_active'] ?? false),
        ]);
        return back()->with('success', 'Fee structure diperbarui.');
    }

    public function deleteStructure(FeeStructure $structure): RedirectResponse
    {
        $this->authorizeOwn($structure);
        $structure->delete();
        return back()->with('success', 'Fee structure dihapus.');
    }

    /* ============== INVOICES ============== */

    public function invoices(Request $request): View
    {
        $schoolId = $this->schoolId();

        $invoices = FeeInvoice::where('school_id', $schoolId)
            ->with(['student.user', 'feeStructure'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->period, fn ($q) => $q->where('period', $request->period))
            ->when($request->search, fn ($q) => $q->where(fn ($s) => $s
                ->where('invoice_no', 'like', "%{$request->search}%")
                ->orWhereHas('student.user', fn ($u) => $u->where('name', 'like', "%{$request->search}%"))))
            ->orderByDesc('due_date')
            ->paginate(30)
            ->withQueryString();

        return view('school-admin.finance.invoices', [
            'invoices'   => $invoices,
            'structures' => FeeStructure::where('school_id', $schoolId)->where('is_active', true)->get(),
            'classSections' => ClassSection::where('school_id', $schoolId)->with(['classRoom', 'section'])->get(),
        ]);
    }

    public function generateInvoices(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fee_structure_id' => 'required|exists:fee_structures,id',
            'class_section_id' => 'nullable|exists:class_sections,id',
            'period'           => 'required|string|max:20',
            'due_date'         => 'required|date',
        ]);

        $schoolId = $this->schoolId();
        $structure = FeeStructure::where('school_id', $schoolId)->findOrFail($data['fee_structure_id']);

        $studentsQuery = Student::where('school_id', $schoolId);
        if (!empty($data['class_section_id'])) {
            $studentsQuery->where('class_section_id', $data['class_section_id']);
        }
        $students = $studentsQuery->get();

        $created = 0;
        DB::transaction(function () use ($students, $structure, $data, $schoolId, &$created) {
            foreach ($students as $student) {
                $exists = FeeInvoice::where('school_id', $schoolId)
                    ->where('student_id', $student->id)
                    ->where('fee_structure_id', $structure->id)
                    ->where('period', $data['period'])
                    ->exists();
                if ($exists) continue;

                FeeInvoice::create([
                    'school_id'        => $schoolId,
                    'student_id'       => $student->id,
                    'fee_structure_id' => $structure->id,
                    'invoice_no'       => 'INV-'.strtoupper(Str::random(10)),
                    'due_date'         => $data['due_date'],
                    'amount'           => $structure->amount,
                    'status'           => 'unpaid',
                    'period'           => $data['period'],
                ]);
                $created++;
            }
        });

        return back()->with('success', "$created invoice baru dibuat (sisanya sudah ada).");
    }

    public function showInvoice(FeeInvoice $invoice): View
    {
        $this->authorizeOwn($invoice);
        return view('school-admin.finance.invoice-show', [
            'invoice'  => $invoice->load(['student.user', 'feeStructure', 'payments.collector', 'installments', 'refunds.refundedBy']),
        ]);
    }

    public function createInstallments(Request $request, FeeInvoice $invoice): RedirectResponse
    {
        $this->authorizeOwn($invoice);

        $data = $request->validate([
            'count' => 'required|integer|min:2|max:24',
        ]);

        $dueDates = $request->input('due_dates', []);

        $this->installments->createSchedule($invoice, (int) $data['count'], $dueDates);

        return back()->with('success', 'Jadwal cicilan dibuat.');
    }

    public function payInstallment(Request $request, FeeInstallment $installment): RedirectResponse
    {
        abort_unless($installment->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'amount_rupiah'  => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:50',
            'reference'      => 'nullable|string|max:200',
        ]);

        $this->installments->pay($installment, (int) round($data['amount_rupiah'] * 100), $data['payment_method'], $data['reference'] ?? null);

        return back()->with('success', 'Cicilan dilunasi.');
    }

    public function refund(Request $request, FeeInvoice $invoice): RedirectResponse
    {
        $this->authorizeOwn($invoice);

        $data = $request->validate([
            'amount_rupiah' => 'required|numeric|min:0',
            'reason'        => 'nullable|string|max:500',
            'fee_payment_id'=> 'nullable|exists:fee_payments,id',
        ]);

        $amountCents = (int) round($data['amount_rupiah'] * 100);

        $this->refunds->refund($invoice, $amountCents, $data['reason'] ?? '', $data['fee_payment_id'] ?? null);

        app(\App\Services\Finance\AccountingService::class)->postRefund($invoice->school_id, $amountCents, $data['fee_payment_id'] ?? null);

        return back()->with('success', 'Refund tercatat.');
    }

    public function applyLateFee(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'daily_rate_rupiah' => 'required|numeric|min:0|max:1000000',
        ]);

        $count = $this->refunds->applyLateFee($this->schoolId(), (int) round($data['daily_rate_rupiah'] * 100));

        return back()->with('success', "Denda keterlambatan diterapkan ke $count cicilan.");
    }

    public function deleteInvoice(FeeInvoice $invoice): RedirectResponse
    {
        $this->authorizeOwn($invoice);
        if ($invoice->paid_amount > 0) {
            return back()->withErrors('Invoice sudah ada pembayaran, tidak bisa dihapus.');
        }
        $invoice->delete();
        return redirect()->route('admin.fee.invoices.index')->with('success', 'Invoice dihapus.');
    }

    /* ============== PAYMENTS ============== */

    public function recordPayment(Request $request, FeeInvoice $invoice): RedirectResponse
    {
        $this->authorizeOwn($invoice);

        $data = $request->validate([
            'amount_rupiah'  => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:50',
            'payment_date'   => 'required|date',
            'reference'      => 'nullable|string|max:200',
            'note'           => 'nullable|string|max:500',
        ]);
        $amountCents = (int) ($data['amount_rupiah'] * 100);

        DB::transaction(function () use ($invoice, $data, $amountCents) {
            FeePayment::create([
                'fee_invoice_id' => $invoice->id,
                'collected_by'   => auth()->id(),
                'amount'         => $amountCents,
                'payment_method' => $data['payment_method'],
                'reference'      => $data['reference'] ?? null,
                'note'           => $data['note'] ?? null,
                'payment_date'   => $data['payment_date'],
            ]);

            $newPaid = $invoice->paid_amount + $amountCents;
            $invoice->update([
                'paid_amount' => $newPaid,
                'status'      => $newPaid >= $invoice->amount ? 'paid' : 'partial',
            ]);
        });

        app(\App\Services\Finance\AccountingService::class)->postFeePayment(
            $invoice->school_id, $amountCents, $data['payment_method'], $data['reference'] ?? null, $data['payment_date']
        );

        return back()->with('success', 'Pembayaran tercatat.');
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }
}
