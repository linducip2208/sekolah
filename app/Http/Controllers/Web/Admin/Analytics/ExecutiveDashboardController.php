<?php

namespace App\Http\Controllers\Web\Admin\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Academic\Attendance;
use App\Models\Academic\ReportCard;
use App\Models\Academic\Staff;
use App\Models\Academic\Student;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FeePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExecutiveDashboardController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();
        $monthsBack = (int) $request->get('months', 12);

        $totalStudents = Student::where('school_id', $schoolId)->count();
        $totalTeachers = Staff::where('school_id', $schoolId)->count();

        $currentMonth = Carbon::now()->startOfMonth();
        $startRange = Carbon::now()->subMonths($monthsBack)->startOfMonth();

        $totalRevenue = FeePayment::whereHas('invoice', fn ($q) => $q->where('school_id', $schoolId))
            ->where('payment_date', '>=', $startRange)
            ->sum('amount');

        $overdueInvoices = FeeInvoice::where('school_id', $schoolId)
            ->whereIn('status', ['unpaid', 'overdue'])
            ->where('due_date', '<', Carbon::today())
            ->count();

        $recentDays = 30;
        $attendanceRate = $this->calculateAttendanceRate($schoolId, $recentDays);

        $atRiskCount = \App\Models\Analytics\StudentRiskScore::where('school_id', $schoolId)
            ->whereDate('snapshot_date', Carbon::today())
            ->whereIn('risk_level', ['high', 'critical'])
            ->count();

        $enrollmentTrend = $this->getEnrollmentTrend($schoolId, $monthsBack);
        $revenueTrend = $this->getRevenueTrend($schoolId, $monthsBack);
        $attendanceTrend = $this->getAttendanceTrend($schoolId, $monthsBack);

        $topStudents = ReportCard::where('school_id', $schoolId)
            ->where('is_published', true)
            ->with('student.user:id,name')
            ->orderByDesc('gpa')
            ->limit(5)
            ->get();

        $recentAlerts = collect();
        if ($overdueInvoices > 0) {
            $recentAlerts->push(['type' => 'warning', 'message' => "{$overdueInvoices} invoice overdue"]);
        }
        if ($atRiskCount > 0) {
            $recentAlerts->push(['type' => 'danger', 'message' => "{$atRiskCount} siswa berisiko tinggi"]);
        }
        $expiringContracts = \App\Models\Hr\EmploymentContract::where('school_id', $schoolId)
            ->where('end_date', '>=', Carbon::today())
            ->where('end_date', '<=', Carbon::today()->addDays(30))
            ->where('status', 'active')
            ->count();
        if ($expiringContracts > 0) {
            $recentAlerts->push(['type' => 'info', 'message' => "{$expiringContracts} kontrak habis dalam 30 hari"]);
        }

        return view('school-admin.analytics.executive-dashboard', [
            'totalStudents'    => $totalStudents,
            'totalTeachers'    => $totalTeachers,
            'totalRevenue'     => $totalRevenue,
            'attendanceRate'   => $attendanceRate,
            'enrollmentTrend'  => $enrollmentTrend,
            'revenueTrend'     => $revenueTrend,
            'attendanceTrend'  => $attendanceTrend,
            'topStudents'      => $topStudents,
            'recentAlerts'     => $recentAlerts,
            'monthsBack'       => $monthsBack,
        ]);
    }

    private function calculateAttendanceRate(int $schoolId, int $days): float
    {
        $total = Attendance::where('school_id', $schoolId)
            ->whereDate('date', '>=', Carbon::now()->subDays($days))
            ->count();
        if ($total === 0) return 0;
        $present = Attendance::where('school_id', $schoolId)
            ->whereDate('date', '>=', Carbon::now()->subDays($days))
            ->where('status', 'present')
            ->count();
        return round($present / $total * 100, 1);
    }

    private function getEnrollmentTrend(int $schoolId, int $months): array
    {
        $data = Student::where('school_id', $schoolId)
            ->where('created_at', '>=', Carbon::now()->subMonths($months))
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('count(*) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $labels = $values = [];
        for ($i = $months; $i >= 0; $i--) {
            $key = Carbon::now()->subMonths($i)->format('Y-m');
            $labels[] = Carbon::now()->subMonths($i)->format('M Y');
            $values[] = $data[$key] ?? 0;
        }
        return ['labels' => $labels, 'values' => $values];
    }

    private function getRevenueTrend(int $schoolId, int $months): array
    {
        $data = FeePayment::whereHas('invoice', fn ($q) => $q->where('school_id', $schoolId))
            ->where('payment_date', '>=', Carbon::now()->subMonths($months))
            ->select(DB::raw("DATE_FORMAT(payment_date, '%Y-%m') as month"), DB::raw('sum(amount) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $labels = $values = [];
        for ($i = $months; $i >= 0; $i--) {
            $key = Carbon::now()->subMonths($i)->format('Y-m');
            $labels[] = Carbon::now()->subMonths($i)->format('M Y');
            $values[] = (int) ($data[$key] ?? 0);
        }
        return ['labels' => $labels, 'values' => $values];
    }

    private function getAttendanceTrend(int $schoolId, int $months): array
    {
        $labels = $values = [];
        for ($i = $months; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $labels[] = $start->format('M Y');

            $total = Attendance::where('school_id', $schoolId)
                ->whereBetween('date', [$start, $end])->count();
            $present = Attendance::where('school_id', $schoolId)
                ->whereBetween('date', [$start, $end])
                ->where('status', 'present')->count();
            $values[] = $total > 0 ? round($present / $total * 100, 1) : 0;
        }
        return ['labels' => $labels, 'values' => $values];
    }
}
