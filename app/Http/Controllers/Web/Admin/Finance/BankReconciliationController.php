<?php

namespace App\Http\Controllers\Web\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\BankStatement;
use App\Models\Finance\FeePayment;
use App\Services\Finance\BankReconciliationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankReconciliationController extends Controller
{
    public function __construct(private BankReconciliationService $service) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $statements = BankStatement::where('school_id', $schoolId)
            ->with('feePayment.invoice.student.user')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $unmatchedPayments = FeePayment::whereHas('invoice', fn ($q) => $q->where('school_id', $schoolId))
            ->with('invoice.student.user')
            ->orderByDesc('payment_date')
            ->limit(200)
            ->get();

        return view('school-admin.finance.bank-reconciliation', [
            'statements'       => $statements,
            'unmatchedPayments'=> $unmatchedPayments,
            'summary'          => $this->service->summary($schoolId),
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bank_account' => 'required|string|max:100',
            'lines'        => 'required|array|min:1',
            'lines.*.transaction_date' => 'required|date',
            'lines.*.description'      => 'nullable|string|max:500',
            'lines.*.reference_no'     => 'nullable|string|max:100',
            'lines.*.amount'           => 'required|numeric',
        ]);

        $lines = collect($data['lines'])->map(fn ($l) => [
            'transaction_date' => $l['transaction_date'],
            'description'      => $l['description'] ?? null,
            'reference_no'     => $l['reference_no'] ?? null,
            'amount'           => (int) round((float) $l['amount'] * 100),
        ])->all();

        $count = $this->service->addLines($this->schoolId(), $data['bank_account'], $lines);

        return back()->with('success', "$count baris rekening koran diimpor.");
    }

    public function match(Request $request, BankStatement $statement): RedirectResponse
    {
        abort_unless($statement->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'fee_payment_id' => 'required|exists:fee_payments,id',
        ]);

        $this->service->match($statement, (int) $data['fee_payment_id']);

        return back()->with('success', 'Transaksi dicocokkan.');
    }

    public function unmatch(BankStatement $statement): RedirectResponse
    {
        abort_unless($statement->school_id === $this->schoolId(), 403);

        $this->service->unmatch($statement);

        return back()->with('success', 'Pencocokan dibatalkan.');
    }
}
