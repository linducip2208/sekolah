<?php

namespace App\Http\Controllers\Web\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\JournalEntry;
use App\Services\Finance\AccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountingController extends Controller
{
    public function __construct(private AccountingService $service) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }

    /* ==================== CHART OF ACCOUNTS ==================== */

    public function coa(): View
    {
        $schoolId = $this->schoolId();

        $accounts = ChartOfAccount::where('school_id', $schoolId)
            ->orderBy('code')
            ->get()
            ->groupBy('type');

        return view('school-admin.finance.accounting.coa', [
            'accounts' => $accounts,
            'types'    => ChartOfAccount::TYPES,
            'typeLabels' => [
                'asset' => 'Aset', 'liability' => 'Kewajiban', 'equity' => 'Ekuitas',
                'revenue' => 'Pendapatan', 'expense' => 'Beban',
            ],
        ]);
    }

    public function storeAccount(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code'           => 'required|string|max:20',
            'name'           => 'required|string|max:200',
            'type'           => 'required|in:asset,liability,equity,revenue,expense',
            'normal_balance' => 'required|in:debit,credit',
            'parent_id'      => 'nullable|exists:chart_of_accounts,id',
        ]);

        ChartOfAccount::create(array_merge($data, [
            'school_id' => $this->schoolId(),
            'is_active' => true,
        ]));

        return back()->with('success', 'Akun ditambahkan.');
    }

    public function updateAccount(Request $request, ChartOfAccount $account): RedirectResponse
    {
        $this->authorizeOwn($account);

        $data = $request->validate([
            'code'           => 'required|string|max:20',
            'name'           => 'required|string|max:200',
            'type'           => 'required|in:asset,liability,equity,revenue,expense',
            'normal_balance' => 'required|in:debit,credit',
        ]);

        $account->update($data);

        return back()->with('success', 'Akun diperbarui.');
    }

    public function deleteAccount(ChartOfAccount $account): RedirectResponse
    {
        $this->authorizeOwn($account);

        abort_if($account->lines()->exists(), 422, 'Akun tidak dapat dihapus karena sudah dipakai di jurnal.');

        $account->delete();

        return back()->with('success', 'Akun dihapus.');
    }

    public function seedCoa(): RedirectResponse
    {
        $count = $this->service->seedDefaultCoa($this->schoolId());

        return back()->with('success', $count > 0 ? "$count akun default dibuat." : 'Bagan akun sudah ada.');
    }

    /* ==================== JOURNAL ==================== */

    public function journal(Request $request): View
    {
        $schoolId = $this->schoolId();

        $entries = JournalEntry::where('school_id', $schoolId)
            ->with('lines.account')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $accounts = ChartOfAccount::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('school-admin.finance.accounting.journal', compact('entries', 'accounts'));
    }

    public function storeJournal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'entry_date'   => 'required|date',
            'reference_no' => 'nullable|string|max:100',
            'description'  => 'nullable|string|max:500',
            'lines'        => 'required|array|min:2',
            'lines.*.chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit'  => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.description' => 'nullable|string|max:500',
        ]);

        $lines = collect($data['lines'])->map(fn ($l) => [
            'chart_of_account_id' => $l['chart_of_account_id'],
            'debit'               => (int) round(((float) ($l['debit'] ?? 0)) * 100),
            'credit'              => (int) round(((float) ($l['credit'] ?? 0)) * 100),
            'description'         => $l['description'] ?? null,
        ])->all();

        abort_if(!$this->service->isBalanced($lines), 422, 'Jurnal tidak seimbang: total debit harus sama dengan total kredit.');

        $this->service->createEntry($this->schoolId(), [
            'entry_date'   => $data['entry_date'],
            'reference_no' => $data['reference_no'] ?? null,
            'description'  => $data['description'] ?? null,
        ], $lines);

        return back()->with('success', 'Jurnal dibuat (draft).');
    }

    public function showJournal(JournalEntry $entry): View
    {
        $this->authorizeOwn($entry);

        return view('school-admin.finance.accounting.journal-show', [
            'entry' => $entry->load('lines.account'),
        ]);
    }

    public function postJournal(JournalEntry $entry): RedirectResponse
    {
        $this->authorizeOwn($entry);

        $this->service->post($entry);

        return back()->with('success', 'Jurnal diposting.');
    }

    public function deleteJournal(JournalEntry $entry): RedirectResponse
    {
        $this->authorizeOwn($entry);

        abort_if($entry->status === 'posted', 422, 'Jurnal yang sudah diposting tidak dapat dihapus.');

        $entry->lines()->delete();
        $entry->delete();

        return back()->with('success', 'Jurnal dihapus.');
    }

    /* ==================== REPORTS ==================== */

    public function trialBalance(Request $request): View
    {
        $from = $request->from;
        $to   = $request->to;

        return view('school-admin.finance.accounting.trial-balance', [
            'rows' => $this->service->trialBalance($this->schoolId(), $from, $to),
            'from' => $from,
            'to'   => $to,
        ]);
    }

    public function profitLoss(Request $request): View
    {
        return view('school-admin.finance.accounting.profit-loss', [
            'pl'   => $this->service->profitLoss($this->schoolId(), $request->from, $request->to),
            'from' => $request->from,
            'to'   => $request->to,
        ]);
    }

    public function balanceSheet(Request $request): View
    {
        return view('school-admin.finance.accounting.balance-sheet', [
            'bs'   => $this->service->balanceSheet($this->schoolId(), $request->as_of),
            'asOf' => $request->as_of,
        ]);
    }
}
