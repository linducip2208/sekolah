<?php

namespace App\Services\Finance;

use App\Models\Finance\FeeInstallment;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeeRefund;
use Illuminate\Support\Facades\DB;

class FeeRefundService
{
    /** Refund a portion of what has been paid on an invoice. */
    public function refund(FeeInvoice $invoice, int $amount, string $reason, ?int $paymentId = null): FeeRefund
    {
        abort_if($amount <= 0, 422, 'Jumlah refund tidak valid.');
        abort_if($amount > $invoice->paid_amount, 422, 'Refund melebihi jumlah yang sudah dibayar.');

        $refund = null;

        DB::transaction(function () use ($invoice, $amount, $reason, $paymentId, &$refund) {
            $refund = FeeRefund::create([
                'school_id'      => $invoice->school_id,
                'fee_invoice_id' => $invoice->id,
                'fee_payment_id' => $paymentId,
                'amount'         => $amount,
                'reason'         => $reason,
                'refunded_by'    => auth()->id(),
                'refunded_at'    => now()->toDateString(),
            ]);

            $newPaid = $invoice->paid_amount - $amount;
            $invoice->update([
                'paid_amount' => $newPaid,
                'status'      => $newPaid <= 0 ? 'unpaid' : 'partial',
            ]);
        });

        return $refund;
    }

    /** Apply a daily late fee to overdue installments. Returns count updated. */
    public function applyLateFee(int $schoolId, int $dailyRateCents): int
    {
        $overdue = FeeInstallment::where('school_id', $schoolId)
            ->where('status', 'overdue')
            ->whereNotNull('due_date')
            ->get();

        $count = 0;
        foreach ($overdue as $installment) {
            $days = max(1, $installment->due_date->diffInDays(now()->startOfDay()));
            $fee  = $days * $dailyRateCents;

            $installment->update(['late_fee' => $fee]);
            $count++;
        }

        return $count;
    }
}
