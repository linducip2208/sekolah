<?php

namespace App\Services\Finance;

use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\JournalEntry;
use App\Models\Finance\JournalEntryLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    public const DEFAULT_COA = [
        ['code' => '1000', 'name' => 'Kas',                    'type' => 'asset',    'normal_balance' => 'debit'],
        ['code' => '1100', 'name' => 'Bank',                   'type' => 'asset',    'normal_balance' => 'debit'],
        ['code' => '1200', 'name' => 'Piutang Usaha',          'type' => 'asset',    'normal_balance' => 'debit'],
        ['code' => '1300', 'name' => 'Persediaan',             'type' => 'asset',    'normal_balance' => 'debit'],
        ['code' => '1500', 'name' => 'Aset Tetap',             'type' => 'asset',    'normal_balance' => 'debit'],
        ['code' => '2000', 'name' => 'Hutang Usaha',           'type' => 'liability','normal_balance' => 'credit'],
        ['code' => '2100', 'name' => 'Hutang Lainnya',         'type' => 'liability','normal_balance' => 'credit'],
        ['code' => '3000', 'name' => 'Modal',                  'type' => 'equity',   'normal_balance' => 'credit'],
        ['code' => '3100', 'name' => 'Laba Ditahan',           'type' => 'equity',   'normal_balance' => 'credit'],
        ['code' => '4000', 'name' => 'Pendapatan SPP',         'type' => 'revenue',  'normal_balance' => 'credit'],
        ['code' => '4100', 'name' => 'Pendapatan Lainnya',     'type' => 'revenue',  'normal_balance' => 'credit'],
        ['code' => '5000', 'name' => 'Beban Gaji',             'type' => 'expense',  'normal_balance' => 'debit'],
        ['code' => '5100', 'name' => 'Beban Operasional',      'type' => 'expense',  'normal_balance' => 'debit'],
        ['code' => '5200', 'name' => 'Beban Lainnya',          'type' => 'expense',  'normal_balance' => 'debit'],
    ];

    /** Seed the default chart of accounts for a school (idempotent). */
    public function seedDefaultCoa(int $schoolId): int
    {
        $existing = ChartOfAccount::where('school_id', $schoolId)->count();
        if ($existing > 0) {
            return 0;
        }

        $created = 0;
        foreach (self::DEFAULT_COA as $account) {
            ChartOfAccount::create(array_merge($account, ['school_id' => $schoolId]));
            $created++;
        }

        return $created;
    }

    public function isBalanced(array $lines): bool
    {
        $debit  = (int) collect($lines)->sum('debit');
        $credit = (int) collect($lines)->sum('credit');

        return $debit > 0 && $debit === $credit;
    }

    public function createEntry(int $schoolId, array $header, array $lines): JournalEntry
    {
        return DB::transaction(function () use ($schoolId, $header, $lines) {
            $entry = JournalEntry::create(array_merge($header, [
                'school_id'  => $schoolId,
                'status'     => 'draft',
                'created_by' => auth()->id(),
            ]));

            foreach ($lines as $line) {
                if (empty($line['chart_of_account_id']) || ((int) ($line['debit'] ?? 0) === 0 && (int) ($line['credit'] ?? 0) === 0)) {
                    continue;
                }
                JournalEntryLine::create([
                    'school_id'           => $schoolId,
                    'journal_entry_id'    => $entry->id,
                    'chart_of_account_id' => $line['chart_of_account_id'],
                    'debit'               => (int) ($line['debit'] ?? 0),
                    'credit'              => (int) ($line['credit'] ?? 0),
                    'description'         => $line['description'] ?? null,
                ]);
            }

            return $entry;
        });
    }

    public function post(JournalEntry $entry): void
    {
        abort_if($entry->status === 'posted', 422, 'Jurnal sudah diposting.');

        $debit  = (int) $entry->lines()->sum('debit');
        $credit = (int) $entry->lines()->sum('credit');

        abort_if($debit === 0 || $debit !== $credit, 422, 'Jurnal tidak seimbang (debit ≠ kredit).');

        $entry->update([
            'status'    => 'posted',
            'posted_by' => auth()->id(),
            'posted_at' => now(),
        ]);
    }

    public function trialBalance(int $schoolId, ?string $from = null, ?string $to = null): Collection
    {
        $lines = $this->postedLines($schoolId, $from, $to)
            ->groupBy('chart_of_account_id')
            ->map(function ($group) {
                $account = $group->first()->account;

                $debit  = (int) $group->sum('debit');
                $credit = (int) $group->sum('credit');

                $balance = $account->normal_balance === 'debit'
                    ? $debit - $credit
                    : $credit - $debit;

                return (object) [
                    'code'         => $account->code,
                    'name'         => $account->name,
                    'type'         => $account->type,
                    'normal_balance' => $account->normal_balance,
                    'debit'        => $debit,
                    'credit'       => $credit,
                    'balance'      => $balance,
                ];
            })
            ->sortBy('code')
            ->values();

        return $lines;
    }

    public function profitLoss(int $schoolId, ?string $from = null, ?string $to = null): array
    {
        $tb = $this->trialBalance($schoolId, $from, $to);

        $revenue = $tb->whereIn('type', ['revenue'])->sum('balance');
        $expense = $tb->whereIn('type', ['expense'])->sum('balance');

        return [
            'revenue'   => $revenue,
            'expense'   => $expense,
            'net_income'=> $revenue - $expense,
            'revenue_accounts' => $tb->whereIn('type', ['revenue'])->values(),
            'expense_accounts' => $tb->whereIn('type', ['expense'])->values(),
        ];
    }

    public function balanceSheet(int $schoolId, ?string $asOf = null): array
    {
        $tb = $this->trialBalance($schoolId, null, $asOf);

        $assets     = $tb->whereIn('type', ['asset'])->sum('balance');
        $liabilities= $tb->whereIn('type', ['liability'])->sum('balance');
        $equity     = $tb->whereIn('type', ['equity'])->sum('balance');

        $netIncome  = $this->profitLoss($schoolId, null, $asOf)['net_income'];

        $totalEquity = $equity + $netIncome;

        return [
            'assets'         => $assets,
            'liabilities'    => $liabilities,
            'equity'         => $equity,
            'net_income'     => $netIncome,
            'total_equity'   => $totalEquity,
            'liabilities_plus_equity' => $liabilities + $totalEquity,
            'asset_accounts'      => $tb->whereIn('type', ['asset'])->values(),
            'liability_accounts'  => $tb->whereIn('type', ['liability'])->values(),
            'equity_accounts'     => $tb->whereIn('type', ['equity'])->values(),
        ];
    }

    /** Auto-post a journal entry for a fee payment (Debit Kas/Bank, Credit Pendapatan). */
    public function postFeePayment(int $schoolId, int $amountCents, string $method, ?string $reference = null, ?string $date = null): void
    {
        if ($amountCents <= 0) {
            return;
        }

        $assetCode = in_array($method, ['bank_transfer', 'va', 'qris', 'ewallet'], true) ? '1100' : '1000';
        $asset   = ChartOfAccount::where('school_id', $schoolId)->where('code', $assetCode)->first();
        $revenue = ChartOfAccount::where('school_id', $schoolId)->where('code', '4000')->first();

        if (!$asset || !$revenue) {
            return;
        }

        $entry = $this->createEntry($schoolId, [
            'entry_date'   => $date ?? now()->toDateString(),
            'reference_no' => $reference,
            'description'  => 'Pembayaran SPP (otomatis)',
        ], [
            ['chart_of_account_id' => $asset->id,   'debit' => $amountCents, 'credit' => 0],
            ['chart_of_account_id' => $revenue->id, 'debit' => 0,            'credit' => $amountCents],
        ]);

        $this->post($entry);
    }

    /** Auto-post a journal entry for a refund (Debit Pendapatan, Credit Kas). */
    public function postRefund(int $schoolId, int $amountCents, ?string $reference = null): void
    {
        if ($amountCents <= 0) {
            return;
        }

        $asset   = ChartOfAccount::where('school_id', $schoolId)->where('code', '1000')->first();
        $revenue = ChartOfAccount::where('school_id', $schoolId)->where('code', '4000')->first();

        if (!$asset || !$revenue) {
            return;
        }

        $entry = $this->createEntry($schoolId, [
            'entry_date'   => now()->toDateString(),
            'reference_no' => $reference,
            'description'  => 'Refund SPP (otomatis)',
        ], [
            ['chart_of_account_id' => $revenue->id, 'debit' => $amountCents, 'credit' => 0],
            ['chart_of_account_id' => $asset->id,   'debit' => 0,            'credit' => $amountCents],
        ]);

        $this->post($entry);
    }

    public function ledger(int $schoolId, int $accountId, ?string $from = null, ?string $to = null): Collection
    {
        return JournalEntryLine::where('school_id', $schoolId)
            ->where('chart_of_account_id', $accountId)
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')
                ->when($from, fn ($qq) => $qq->whereDate('entry_date', '>=', $from))
                ->when($to, fn ($qq) => $qq->whereDate('entry_date', '<=', $to)))
            ->with('journalEntry')
            ->orderBy('id')
            ->get();
    }

    private function postedLines(int $schoolId, ?string $from = null, ?string $to = null): Collection
    {
        return JournalEntryLine::with('account')
            ->where('school_id', $schoolId)
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')
                ->when($from, fn ($qq) => $qq->whereDate('entry_date', '>=', $from))
                ->when($to, fn ($qq) => $qq->whereDate('entry_date', '<=', $to)))
            ->get();
    }
}
