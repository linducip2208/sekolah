<?php

namespace App\Http\Controllers\Web\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Services\SuperAdminService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceReportController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function summary(Request $request): View
    {
        $schoolId = $this->schoolId();
        $from = $request->from ? Carbon::parse($request->from)->startOfDay() : now()->startOfYear();
        $to   = $request->to   ? Carbon::parse($request->to)->endOfDay()   : now()->endOfDay();

        // ===== INCOME =====
        // SPP / fee invoice payments
        $sppCollected = (int) DB::table('fee_payments')
            ->join('fee_invoices', 'fee_payments.fee_invoice_id', '=', 'fee_invoices.id')
            ->where('fee_invoices.school_id', $schoolId)
            ->whereBetween('fee_payments.payment_date', [$from, $to])
            ->sum('fee_payments.amount');

        // Donations completed
        $donationsReceived = (int) DB::table('donations')
            ->where('school_id', $schoolId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');

        // Event ticket revenue
        $eventRevenue = (int) DB::table('event_rsvps')
            ->join('school_events', 'event_rsvps.school_event_id', '=', 'school_events.id')
            ->where('event_rsvps.school_id', $schoolId)
            ->where('event_rsvps.status', 'going')
            ->whereBetween('event_rsvps.created_at', [$from, $to])
            ->selectRaw('SUM(school_events.ticket_price * event_rsvps.guests_count) as total')
            ->value('total') ?? 0;

        $totalIncome = $sppCollected + $donationsReceived + (int)$eventRevenue;

        // ===== EXPENSE =====
        // Payroll paid
        $payrollPaid = (int) DB::table('salary_slips')
            ->where('school_id', $schoolId)
            ->where('status', 'paid')
            ->whereBetween('paid_on', [$from, $to])
            ->sum('net_salary');

        // Maintenance costs
        $maintenanceCost = (int) DB::table('maintenance_requests')
            ->where('school_id', $schoolId)
            ->whereNotNull('cost')
            ->whereBetween('resolved_at', [$from, $to])
            ->sum('cost');

        $totalExpense = $payrollPaid + $maintenanceCost;
        $netCash = $totalIncome - $totalExpense;

        // ===== OUTSTANDING =====
        $outstanding = (int) DB::table('fee_invoices')
            ->where('school_id', $schoolId)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->selectRaw('SUM(amount - paid_amount) as total')
            ->value('total') ?? 0;

        $outstandingCount = DB::table('fee_invoices')
            ->where('school_id', $schoolId)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->count();

        // ===== Monthly breakdown (12 months) =====
        $monthExpr = SuperAdminService::monthExpr('fee_payments.payment_date');
        $monthlySpp = DB::table('fee_payments')
            ->join('fee_invoices', 'fee_payments.fee_invoice_id', '=', 'fee_invoices.id')
            ->where('fee_invoices.school_id', $schoolId)
            ->where('fee_payments.payment_date', '>=', now()->subMonths(12))
            ->selectRaw("$monthExpr as month, SUM(fee_payments.amount) as total")
            ->groupBy('month')->orderBy('month')->get();

        // ===== Per fee structure =====
        $perStructure = DB::table('fee_payments')
            ->join('fee_invoices', 'fee_payments.fee_invoice_id', '=', 'fee_invoices.id')
            ->join('fee_structures', 'fee_invoices.fee_structure_id', '=', 'fee_structures.id')
            ->where('fee_invoices.school_id', $schoolId)
            ->whereBetween('fee_payments.payment_date', [$from, $to])
            ->selectRaw('fee_structures.name as structure_name, SUM(fee_payments.amount) as total, COUNT(*) as cnt')
            ->groupBy('fee_structures.id', 'fee_structures.name')
            ->orderByDesc('total')->get();

        // ===== Per payment method =====
        $perMethod = DB::table('fee_payments')
            ->join('fee_invoices', 'fee_payments.fee_invoice_id', '=', 'fee_invoices.id')
            ->where('fee_invoices.school_id', $schoolId)
            ->whereBetween('fee_payments.payment_date', [$from, $to])
            ->selectRaw('fee_payments.payment_method, SUM(fee_payments.amount) as total, COUNT(*) as cnt')
            ->groupBy('fee_payments.payment_method')
            ->orderByDesc('total')->get();

        return view('school-admin.finance.report-summary', compact(
            'from', 'to',
            'sppCollected', 'donationsReceived', 'eventRevenue', 'totalIncome',
            'payrollPaid', 'maintenanceCost', 'totalExpense', 'netCash',
            'outstanding', 'outstandingCount',
            'monthlySpp', 'perStructure', 'perMethod'
        ));
    }

    public function outstanding(Request $request): View
    {
        $schoolId = $this->schoolId();

        $invoices = DB::table('fee_invoices as fi')
            ->join('students as s', 'fi.student_id', '=', 's.id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->leftJoin('fee_structures as fs', 'fi.fee_structure_id', '=', 'fs.id')
            ->leftJoin('class_sections as cs', 's.class_section_id', '=', 'cs.id')
            ->leftJoin('class_rooms as cr', 'cs.class_room_id', '=', 'cr.id')
            ->leftJoin('sections as sec', 'cs.section_id', '=', 'sec.id')
            ->where('fi.school_id', $schoolId)
            ->whereIn('fi.status', ['unpaid', 'partial', 'overdue'])
            ->selectRaw('fi.id, fi.invoice_no, fi.amount, fi.paid_amount, fi.due_date, fi.period, fi.status,
                u.name as student_name, s.admission_no, fs.name as fee_name,
                cr.name as class_name, sec.name as section_name')
            ->orderBy('fi.due_date')
            ->paginate(50);

        $totalOutstanding = (int) DB::table('fee_invoices')
            ->where('school_id', $schoolId)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->selectRaw('SUM(amount - paid_amount) as total')
            ->value('total') ?? 0;

        return view('school-admin.finance.report-outstanding', compact('invoices', 'totalOutstanding'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $schoolId = $this->schoolId();
        $from = $request->from ? Carbon::parse($request->from)->startOfDay() : now()->startOfYear();
        $to   = $request->to   ? Carbon::parse($request->to)->endOfDay()   : now()->endOfDay();

        $rows = DB::table('fee_payments as fp')
            ->join('fee_invoices as fi', 'fp.fee_invoice_id', '=', 'fi.id')
            ->join('students as s', 'fi.student_id', '=', 's.id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->leftJoin('fee_structures as fs', 'fi.fee_structure_id', '=', 'fs.id')
            ->leftJoin('users as cu', 'fp.collected_by', '=', 'cu.id')
            ->where('fi.school_id', $schoolId)
            ->whereBetween('fp.payment_date', [$from, $to])
            ->orderBy('fp.payment_date')
            ->select('fp.payment_date', 'fi.invoice_no', 'u.name as student', 's.admission_no', 'fs.name as fee_type',
                'fp.amount', 'fp.payment_method', 'fp.reference', 'cu.name as collector')
            ->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tanggal', 'Invoice', 'Siswa', 'NIS', 'Jenis SPP', 'Jumlah (Rp)', 'Metode', 'Referensi', 'Petugas']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->payment_date, $r->invoice_no, $r->student, $r->admission_no, $r->fee_type,
                    number_format($r->amount / 100, 0, '.', ''),
                    $r->payment_method, $r->reference, $r->collector,
                ]);
            }
            fclose($out);
        }, 'laporan-keuangan-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }
}
