<?php

namespace App\Services;

use App\Models\Finance\CooperativeLoan;
use App\Models\Finance\CooperativeInstallment;
use App\Models\Finance\CooperativeMember;
use App\Models\Finance\CooperativeSaving;

class CooperativeService
{
    public function generateInstallmentSchedule(CooperativeLoan $loan): void
    {
        CooperativeInstallment::where('cooperative_loan_id', $loan->id)->delete();

        $principal = $loan->loan_amount;
        $rate = $loan->interest_rate;
        $term = $loan->term_months;

        if ($term <= 0) {
            return;
        }

        if ($rate > 0) {
            $monthlyRate = $rate / 100 / 12;
            $installment = $principal * $monthlyRate * pow(1 + $monthlyRate, $term) / (pow(1 + $monthlyRate, $term) - 1);
        } else {
            $installment = $principal / $term;
        }

        $monthlyInstallment = (int) round($installment);

        $loan->update([
            'monthly_installment' => $monthlyInstallment,
            'end_date' => $loan->start_date->copy()->addMonths($term),
        ]);

        $dueDate = $loan->start_date->copy();

        for ($i = 1; $i <= $term; $i++) {
            CooperativeInstallment::create([
                'cooperative_loan_id' => $loan->id,
                'installment_number' => $i,
                'due_date' => $dueDate->copy(),
                'amount' => $monthlyInstallment,
                'paid_amount' => 0,
                'status' => 'pending',
            ]);
            $dueDate->addMonth();
        }
    }

    public function calculateOverdue(CooperativeLoan $loan): int
    {
        return CooperativeInstallment::where('cooperative_loan_id', $loan->id)
            ->where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->count();
    }

    public function totalOutstanding(int $schoolId): int
    {
        return CooperativeInstallment::whereHas('loan', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })->where('status', 'pending')->sum('amount');
    }

    public function shuProjection(int $schoolId, int $year): array
    {
        $totalLoans = CooperativeLoan::where('school_id', $schoolId)
            ->whereYear('start_date', $year)
            ->whereIn('status', ['active', 'paid_off'])
            ->sum('loan_amount');

        $totalInterest = 0;
        CooperativeLoan::where('school_id', $schoolId)
            ->whereYear('start_date', $year)
            ->whereIn('status', ['active', 'paid_off'])
            ->each(function ($loan) use (&$totalInterest) {
                $totalInterest += (int) ($loan->loan_amount * $loan->interest_rate / 100);
            });

        $totalSavings = CooperativeSaving::where('school_id', $schoolId)
            ->whereYear('transaction_date', $year)
            ->where('transaction_type', 'deposit')
            ->sum('amount');

        $grossSurplus = $totalInterest;

        return [
            'total_loans' => $totalLoans,
            'total_interest' => $totalInterest,
            'total_savings' => $totalSavings,
            'gross_surplus' => $grossSurplus,
            'reserve' => (int) ($grossSurplus * 0.25),
            'member_share' => (int) ($grossSurplus * 0.75),
            'member_count' => CooperativeMember::where('school_id', $schoolId)->where('status', 'active')->count(),
        ];
    }

    public function memberSavingsStatement(CooperativeMember $member): array
    {
        $savings = CooperativeSaving::where('cooperative_member_id', $member->id)
            ->orderBy('transaction_date')
            ->get();

        $totalPokok = 0;
        $totalWajib = 0;
        $totalSukarela = 0;

        foreach ($savings as $s) {
            if ($s->transaction_type === 'deposit') {
                match ($s->savings_type) {
                    'pokok' => $totalPokok += $s->amount,
                    'wajib' => $totalWajib += $s->amount,
                    'sukarela' => $totalSukarela += $s->amount,
                    default => null,
                };
            }
        }

        return [
            'savings' => $savings,
            'total_pokok' => $totalPokok,
            'total_wajib' => $totalWajib,
            'total_sukarela' => $totalSukarela,
            'grand_total' => $totalPokok + $totalWajib + $totalSukarela,
        ];
    }
}
