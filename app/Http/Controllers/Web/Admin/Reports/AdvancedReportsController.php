<?php

namespace App\Http\Controllers\Web\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassSection;
use App\Services\SuperAdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdvancedReportsController extends Controller
{
    private function schoolId(): int { return auth()->user()->school_id; }

    /** SPP aging report — 30/60/90 days */
    public function sppAging(): View
    {
        $schoolId = $this->schoolId();
        $now = now();

        $rows = DB::table('fee_invoices as fi')
            ->join('students as s', 'fi.student_id', '=', 's.id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->where('fi.school_id', $schoolId)
            ->whereIn('fi.status', ['unpaid', 'partial', 'overdue'])
            ->select('fi.*', 'u.name as student_name', 's.admission_no')
            ->get();

        $aging = ['current' => 0, '30d' => 0, '60d' => 0, '90d' => 0, '90plus' => 0];
        $countAging = ['current' => 0, '30d' => 0, '60d' => 0, '90d' => 0, '90plus' => 0];

        foreach ($rows as $r) {
            $sisa = $r->amount - $r->paid_amount;
            $daysOverdue = $now->diffInDays(\Carbon\Carbon::parse($r->due_date), false);
            // diffInDays(future, false) returns positive if future
            // For overdue we want positive number of days past
            $daysOverdue = -$daysOverdue;

            if ($daysOverdue < 1) { $aging['current'] += $sisa; $countAging['current']++; }
            elseif ($daysOverdue <= 30) { $aging['30d'] += $sisa; $countAging['30d']++; }
            elseif ($daysOverdue <= 60) { $aging['60d'] += $sisa; $countAging['60d']++; }
            elseif ($daysOverdue <= 90) { $aging['90d'] += $sisa; $countAging['90d']++; }
            else { $aging['90plus'] += $sisa; $countAging['90plus']++; }
        }

        return view('school-admin.reports.spp-aging', compact('aging', 'countAging', 'rows'));
    }

    /** Attendance % per class section */
    public function attendancePercent(Request $request): View
    {
        $schoolId = $this->schoolId();
        $month = $request->month ?? now()->format('Y-m');
        [$year, $monthNum] = explode('-', $month);

        $rows = DB::table('class_sections as cs')
            ->leftJoin('class_rooms as cr', 'cs.class_room_id', '=', 'cr.id')
            ->leftJoin('sections as sec', 'cs.section_id', '=', 'sec.id')
            ->where('cs.school_id', $schoolId)
            ->select('cs.id', 'cr.name as class_name', 'sec.name as section_name')
            ->get()
            ->map(function ($cs) use ($year, $monthNum, $schoolId) {
                $stats = DB::table('attendances')
                    ->where('school_id', $schoolId)
                    ->where('class_section_id', $cs->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $monthNum)
                    ->selectRaw('status, COUNT(*) as cnt')
                    ->groupBy('status')->pluck('cnt', 'status');
                $present = (int) ($stats['present'] ?? 0);
                $late    = (int) ($stats['late'] ?? 0);
                $absent  = (int) ($stats['absent'] ?? 0);
                $leave   = (int) ($stats['on_leave'] ?? 0);
                $total = $present + $late + $absent + $leave;
                $cs->present = $present;
                $cs->late = $late;
                $cs->absent = $absent;
                $cs->leave = $leave;
                $cs->total = $total;
                $cs->pct = $total > 0 ? round(($present + $late) / $total * 100, 1) : 0;
                return $cs;
            });

        return view('school-admin.reports.attendance-pct', compact('rows', 'month'));
    }

    /** Grade distribution per subject */
    public function gradeDistribution(Request $request): View
    {
        $schoolId = $this->schoolId();

        $rows = DB::table('marks as m')
            ->join('subjects as sub', 'm.subject_id', '=', 'sub.id')
            ->where('m.school_id', $schoolId)
            ->selectRaw('sub.name as subject, m.grade, COUNT(*) as cnt')
            ->groupBy('sub.id', 'sub.name', 'm.grade')
            ->orderBy('sub.name')
            ->get()
            ->groupBy('subject');

        return view('school-admin.reports.grade-distribution', compact('rows'));
    }

    /** Discipline leaderboard — net positive/negative students */
    public function disciplineLeaderboard(): View
    {
        $schoolId = $this->schoolId();

        $rows = DB::table('discipline_records as dr')
            ->join('students as s', 'dr.student_id', '=', 's.id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->where('dr.school_id', $schoolId)
            ->selectRaw('s.id, u.name as student_name, s.admission_no, SUM(dr.points) as total_points, COUNT(*) as record_count')
            ->groupBy('s.id', 'u.name', 's.admission_no')
            ->orderByDesc('total_points')
            ->get();

        return view('school-admin.reports.discipline-leaderboard', compact('rows'));
    }

    /** Cash flow chart with chart.js */
    public function cashFlow(): View
    {
        $schoolId = $this->schoolId();
        $monthExpr = SuperAdminService::monthExpr('payment_date');

        // 12 months income (SPP)
        $income = DB::table('fee_payments as fp')
            ->join('fee_invoices as fi', 'fp.fee_invoice_id', '=', 'fi.id')
            ->where('fi.school_id', $schoolId)
            ->where('fp.payment_date', '>=', now()->subMonths(12))
            ->selectRaw("$monthExpr as month, SUM(fp.amount) as total")
            ->groupBy(DB::raw($monthExpr))
            ->orderBy(DB::raw($monthExpr))
            ->pluck('total', 'month');

        $expenseExpr = SuperAdminService::monthExpr('paid_on');
        $expense = DB::table('salary_slips')
            ->where('school_id', $schoolId)
            ->where('status', 'paid')
            ->where('paid_on', '>=', now()->subMonths(12))
            ->selectRaw("$expenseExpr as month, SUM(net_salary) as total")
            ->groupBy(DB::raw($expenseExpr))
            ->orderBy(DB::raw($expenseExpr))
            ->pluck('total', 'month');

        $months = collect();
        for ($i = 11; $i >= 0; $i--) $months->push(now()->subMonths($i)->format('Y-m'));

        $data = $months->map(fn ($m) => [
            'month'   => $m,
            'income'  => (int) ($income[$m] ?? 0),
            'expense' => (int) ($expense[$m] ?? 0),
            'net'     => (int) ($income[$m] ?? 0) - (int) ($expense[$m] ?? 0),
        ]);

        return view('school-admin.reports.cash-flow', compact('data'));
    }
}
