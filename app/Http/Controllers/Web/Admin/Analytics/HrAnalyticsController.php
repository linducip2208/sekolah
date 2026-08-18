<?php

namespace App\Http\Controllers\Web\Admin\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Academic\Staff;
use App\Models\Academic\Student;
use App\Models\Hr\EmploymentContract;
use App\Models\Hr\KpiAppraisal;
use App\Models\Hr\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HrAnalyticsController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $totalStaff = Staff::where('school_id', $schoolId)->count();
        $totalTeachers = Staff::where('school_id', $schoolId)->where('department', 'teacher')->count();
        $totalStudents = Student::where('school_id', $schoolId)->count();
        $ratio = $totalTeachers > 0 ? round($totalStudents / $totalTeachers, 1) : 0;

        $totalLeaves = LeaveRequest::where('school_id', $schoolId)
            ->whereYear('start_date', Carbon::now()->year)
            ->count();
        $approvedLeaves = LeaveRequest::where('school_id', $schoolId)
            ->whereYear('start_date', Carbon::now()->year)
            ->where('status', 'approved')
            ->count();
        $leaveRate = $totalLeaves > 0 ? round($approvedLeaves / $totalLeaves * 100, 1) : 0;

        $avgSalaryByDept = Staff::where('school_id', $schoolId)
            ->select('department', DB::raw('avg(basic_salary) as avg_salary'), DB::raw('count(*) as count'))
            ->groupBy('department')
            ->having('count', '>=', 1)
            ->get();

        $contractExpiry = EmploymentContract::where('school_id', $schoolId)
            ->where('status', 'active')
            ->where('end_date', '>=', Carbon::today())
            ->where('end_date', '<=', Carbon::now()->addMonths(6))
            ->with('staff.user:id,name')
            ->orderBy('end_date')
            ->get();

        $kpiDistribution = KpiAppraisal::where('school_id', $schoolId)
            ->where('status', 'finalized')
            ->select(
                DB::raw("CASE WHEN total_score >= 80 THEN 'Excellent' WHEN total_score >= 60 THEN 'Good' WHEN total_score >= 40 THEN 'Fair' ELSE 'Needs Improvement' END as label"),
                DB::raw('count(*) as total')
            )
            ->groupBy('label')
            ->get();

        $totalStaffForTraining = Staff::where('school_id', $schoolId)->count();
        $trainedStaff = \App\Models\Academic\TrainingParticipant::whereHas('training', fn ($q) => $q->where('school_id', $schoolId))
            ->distinct('staff_id')
            ->count('staff_id');
        $trainingRate = $totalStaffForTraining > 0 ? round($trainedStaff / $totalStaffForTraining * 100, 1) : 0;

        $leaveByType = LeaveRequest::where('school_id', $schoolId)
            ->whereYear('start_date', Carbon::now()->year)
            ->select('type', DB::raw('sum(days) as total_days'))
            ->groupBy('type')
            ->get();

        return view('school-admin.analytics.hr-analytics', [
            'totalStaff'        => $totalStaff,
            'totalTeachers'     => $totalTeachers,
            'ratio'             => $ratio,
            'leaveRate'         => $leaveRate,
            'avgSalaryByDept'   => $avgSalaryByDept,
            'contractExpiry'    => $contractExpiry,
            'kpiDistribution'   => $kpiDistribution,
            'trainingRate'      => $trainingRate,
            'leaveByType'       => $leaveByType,
        ]);
    }
}
