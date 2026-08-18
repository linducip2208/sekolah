<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Staff;
use App\Models\Finance\SalarySlip;
use App\Models\Hr\EmploymentContract;
use App\Models\Hr\KpiAppraisal;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\OvertimeRecord;
use Illuminate\View\View;

class StaffProfileController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function show(Staff $staff): View
    {
        abort_unless($staff->school_id === $this->schoolId(), 403);

        $staff->load('user.roles');

        $contracts = EmploymentContract::where('school_id', $this->schoolId())
            ->where('staff_id', $staff->id)
            ->orderByDesc('start_date')
            ->get();

        $leaves = LeaveRequest::where('school_id', $this->schoolId())
            ->where('staff_id', $staff->id)
            ->orderByDesc('start_date')
            ->get();

        $overtimes = OvertimeRecord::where('school_id', $this->schoolId())
            ->where('staff_id', $staff->id)
            ->orderByDesc('date')
            ->get();

        $certifications = \App\Models\Academic\TeacherCertification::withoutGlobalScopes()
            ->where('school_id', $this->schoolId())
            ->where('staff_id', $staff->id)
            ->orderByDesc('expiry_date')
            ->get();

        $trainings = \App\Models\Academic\TrainingParticipant::where('staff_id', $staff->user_id)
            ->with('training')
            ->get();

        $salarySlips = SalarySlip::where('school_id', $this->schoolId())
            ->where('staff_id', $staff->id)
            ->orderByDesc('month')
            ->get();

        $kpiAppraisals = KpiAppraisal::where('school_id', $this->schoolId())
            ->where('staff_id', $staff->id)
            ->with('template')
            ->orderByDesc('period')
            ->get();

        return view('school-admin.staff.profile', compact(
            'staff', 'contracts', 'leaves', 'overtimes',
            'certifications', 'trainings', 'salarySlips', 'kpiAppraisals'
        ));
    }
}
