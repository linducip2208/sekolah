<?php

namespace App\Services\Finance;

use App\Models\Finance\FeeInvoice;
use Illuminate\Support\Facades\DB;

/**
 * FeeOverdueService — tandai invoice lewat jatuh tempo sebagai overdue.
 * Dijalankan harian via fee:mark-overdue.
 */
class FeeOverdueService
{
    public function markOverdue(?int $schoolId = null): int
    {
        $query = FeeInvoice::withoutGlobalScopes()
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereDate('due_date', '<', today()->toDateString());

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $count = 0;
        $query->orderBy('id')->chunkById(200, function ($invoices) use (&$count) {
            foreach ($invoices as $invoice) {
                DB::transaction(function () use ($invoice, &$count) {
                    $fresh = FeeInvoice::withoutGlobalScopes()->lockForUpdate()->find($invoice->id);
                    if ($fresh && in_array($fresh->status, ['unpaid', 'partial'], true)) {
                        $fresh->update(['status' => 'overdue']);
                        $count++;
                    }
                });
            }
        });

        return $count;
    }
}
