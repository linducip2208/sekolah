<?php

namespace App\Services\Finance;

use App\Models\Finance\BankStatement;
use App\Models\Finance\FeePayment;

class BankReconciliationService
{
    /** Import raw bank statement lines. Returns number created. */
    public function addLines(int $schoolId, string $bankAccount, array $lines): int
    {
        $count = 0;

        foreach ($lines as $line) {
            if (empty($line['transaction_date']) || empty($line['amount'])) {
                continue;
            }

            BankStatement::create([
                'school_id' => $schoolId,
                'bank_account' => $bankAccount,
                'transaction_date' => $line['transaction_date'],
                'description' => $line['description'] ?? null,
                'reference_no' => $line['reference_no'] ?? null,
                'amount' => (int) $line['amount'],
                'status' => 'unmatched',
            ]);
            $count++;
        }

        return $count;
    }

    /** Match a bank statement line to a recorded payment. */
    public function match(BankStatement $statement, int $paymentId): BankStatement
    {
        $schoolId = (int) auth()->user()->school_id;
        $statement = BankStatement::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->findOrFail($statement->id);
        $payment = FeePayment::whereHas('invoice', fn ($query) => $query->where('school_id', $schoolId))
            ->findOrFail($paymentId);

        // Amounts must align (statement credit = payment amount).
        abort_if($payment->amount !== abs($statement->amount), 422, 'Jumlah tidak cocok antara bank dan pembayaran.');

        $statement->update([
            'status' => 'matched',
            'fee_payment_id' => $payment->id,
            'matched_by' => auth()->id(),
            'matched_at' => now(),
        ]);

        return $statement->fresh();
    }

    /** Unmatch a statement line (re-open for re-matching). */
    public function unmatch(BankStatement $statement): BankStatement
    {
        $statement = BankStatement::where('school_id', auth()->user()->school_id)
            ->findOrFail($statement->id);
        $statement->update([
            'status' => 'unmatched',
            'fee_payment_id' => null,
            'matched_by' => null,
            'matched_at' => null,
        ]);

        return $statement->fresh();
    }

    public function summary(int $schoolId): array
    {
        $unmatched = BankStatement::where('school_id', $schoolId)->where('status', 'unmatched');
        $matched = BankStatement::where('school_id', $schoolId)->where('status', 'matched');

        return [
            'unmatched_count' => (clone $unmatched)->count(),
            'matched_count' => (clone $matched)->count(),
            'unmatched_credit' => (int) (clone $unmatched)->where('amount', '>', 0)->sum('amount'),
            'matched_credit' => (int) (clone $matched)->where('amount', '>', 0)->sum('amount'),
            'unmatched_total' => (int) (clone $unmatched)->sum('amount'),
        ];
    }
}
