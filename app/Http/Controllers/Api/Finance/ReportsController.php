<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Api\Concerns\ConvertsRupiah;
use App\Http\Controllers\Controller;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeePayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Mobile finance reports (docs §admin §3.5-§3.7).
 * Mobile contract: whole rupiah.
 */
class ReportsController extends Controller
{
    use ConvertsRupiah;

    /**
     * Cash summary: collected this month + year, pending, per-method split.
     */
    public function cashSummary(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'accounting.view');
        $schoolId = $request->user()->school_id;

        $monthStart = now()->startOfMonth();

        $collectedMonth = FeePayment::where('school_id', $schoolId)
            ->where('payment_date', '>=', $monthStart->toDateString())->sum('amount');
        $collectedYear = FeePayment::where('school_id', $schoolId)
            ->whereYear('payment_date', now()->year)->sum('amount');
        $pending = FeeInvoice::where('school_id', $schoolId)
            ->where('status', '!=', 'paid')
            ->sum(\DB::raw('amount - COALESCE(paid_amount, 0)'));
        $byMethod = FeePayment::where('school_id', $schoolId)
            ->where('payment_date', '>=', $monthStart->toDateString())
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')->get();

        return response()->json($this->inRupiah([
            'collected_month' => (int) $collectedMonth,
            'collected_year' => (int) $collectedYear,
            'pending' => (int) $pending,
            'by_method' => $byMethod,
        ]));
    }

    /**
     * SPP aging buckets (current, 1-30, 31-60, 61-90, 90+ days overdue).
     */
    public function aging(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'accounting.view');
        $schoolId = $request->user()->school_id;

        $invoices = FeeInvoice::where('school_id', $schoolId)
            ->where('status', '!=', 'paid')
            ->whereNotNull('due_date')->get();

        $buckets = ['current' => 0, 'd1_30' => 0, 'd31_60' => 0, 'd61_90' => 0, 'd90_plus' => 0];
        $today = now()->startOfDay();
        foreach ($invoices as $inv) {
            $outstanding = max(0, (int) $inv->amount - (int) $inv->paid_amount - (int) $inv->discount);
            if ($outstanding <= 0) {
                continue;
            }
            $days = $today->diffInDays(\Carbon\Carbon::parse($inv->due_date)->startOfDay(), false);
            // diffInDays(..., false): negative when due_date is in the past.
            $overdue = $days < 0 ? -$days : 0;
            $bucket = match (true) {
                $overdue <= 0 => 'current',
                $overdue <= 30 => 'd1_30',
                $overdue <= 60 => 'd31_60',
                $overdue <= 90 => 'd61_90',
                default => 'd90_plus',
            };
            $buckets[$bucket] += $outstanding;
        }

        return response()->json($this->inRupiah($buckets));
    }

    /**
     * Outstanding list (top debtors).
     */
    public function outstanding(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'accounting.view');

        $rows = FeeInvoice::where('school_id', $request->user()->school_id)
            ->where('status', '!=', 'paid')
            ->with('feeStructure:id,name')
            ->orderByDesc('due_date')
            ->paginate(50);

        return response()->json($this->inRupiah($rows));
    }

    private function requirePermission(Request $request, string $permission): void
    {
        abort_unless(
            $request->user()->hasRole('super_admin') || $request->user()->can($permission),
            403,
            'Tidak memiliki izin laporan keuangan.'
        );
    }
}
