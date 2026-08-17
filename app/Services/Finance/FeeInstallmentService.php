<?php

namespace App\Services\Finance;

use App\Models\Finance\FeeInstallment;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeePayment;
use Illuminate\Support\Facades\DB;

class FeeInstallmentService
{
    /** Split an invoice into equal installments. Returns the created collection. */
    public function createSchedule(FeeInvoice $invoice, int $count, array $dueDates = []): array
    {
        abort_if($invoice->installments()->exists(), 422, 'Jadwal cicilan sudah ada.');
        abort_if($count < 2, 422, 'Jumlah cicilan minimal 2.');

        $base       = intdiv($invoice->amount, $count);
        $remainder  = $invoice->amount % $count;

        $created = [];

        DB::transaction(function () use ($invoice, $count, $base, $remainder, $dueDates, &$created) {
            for ($i = 1; $i <= $count; $i++) {
                $amount = $base + ($i <= $remainder ? 1 : 0);

                $created[] = FeeInstallment::create([
                    'school_id'     => $invoice->school_id,
                    'fee_invoice_id'=> $invoice->id,
                    'installment_no'=> $i,
                    'amount'        => $amount,
                    'due_date'      => $dueDates[$i - 1] ?? null,
                    'status'        => 'pending',
                ]);
            }
        });

        return $created;
    }

    /** Mark an installment paid and record a payment against the invoice. */
    public function pay(FeeInstallment $installment, int $amount, string $method = 'cash', ?string $reference = null): FeeInstallment
    {
        abort_if($installment->status === 'paid', 422, 'Cicilan sudah lunas.');
        abort_if($amount <= 0, 422, 'Jumlah pembayaran tidak valid.');

        $invoice = $installment->invoice;

        DB::transaction(function () use ($installment, $invoice, $amount, $method, $reference) {
            FeePayment::create([
                'fee_invoice_id' => $invoice->id,
                'collected_by'   => auth()->id(),
                'amount'         => $amount,
                'payment_method' => $method,
                'reference'      => $reference,
                'payment_date'   => now()->toDateString(),
            ]);

            $installment->update([
                'paid_amount' => $installment->paid_amount + $amount,
                'status'      => 'paid',
                'paid_at'     => now()->toDateString(),
            ]);

            $newPaid = $invoice->paid_amount + $amount;
            $invoice->update([
                'paid_amount' => $newPaid,
                'status'      => $newPaid >= $invoice->amount ? 'paid' : 'partial',
            ]);
        });

        return $installment->fresh();
    }

    /** Mark pending installments past their due date as overdue. Returns count. */
    public function applyOverdue(int $schoolId): int
    {
        $updated = FeeInstallment::where('school_id', $schoolId)
            ->where('status', 'pending')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        // Reflect overdue on the parent invoice when it has an overdue installment.
        $overdueInvoiceIds = FeeInstallment::where('school_id', $schoolId)
            ->where('status', 'overdue')
            ->pluck('fee_invoice_id')
            ->unique();

        if ($overdueInvoiceIds->isNotEmpty()) {
            FeeInvoice::whereIn('id', $overdueInvoiceIds)
                ->where('status', 'partial')
                ->update(['status' => 'overdue']);
        }

        return $updated;
    }
}
