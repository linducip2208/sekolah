<?php

namespace App\Http\Controllers\Web\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Academic\Staff;
use App\Models\Hr\EmploymentContract;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\OvertimeRecord;
use App\Services\Hr\HrService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrController extends Controller
{
    public function __construct(private HrService $service) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }

    public function index(): View
    {
        $schoolId = $this->schoolId();

        $contracts = EmploymentContract::where('school_id', $schoolId)
            ->with('staff.user:id,name')
            ->orderByDesc('start_date')
            ->limit(30)
            ->get();

        $leaves = LeaveRequest::where('school_id', $schoolId)
            ->with('staff.user:id,name')
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        $overtimes = OvertimeRecord::where('school_id', $schoolId)
            ->with('staff.user:id,name')
            ->orderByDesc('date')
            ->limit(30)
            ->get();

        $staff = Staff::where('school_id', $schoolId)->with('user:id,name')->orderBy('employee_id')->get();

        return view('school-admin.hr.index', compact('contracts', 'leaves', 'overtimes', 'staff'));
    }

    public function storeContract(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_id'    => 'required|exists:staffs,id',
            'type'        => 'required|in:permanent,contract,probation',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'salary_rupiah' => 'required|numeric|min:0',
        ]);

        $this->service->createContract($this->schoolId(), [
            'staff_id'   => $data['staff_id'],
            'type'       => $data['type'],
            'start_date' => $data['start_date'],
            'end_date'   => $data['end_date'] ?? null,
            'salary'     => (int) round($data['salary_rupiah'] * 100),
            'status'     => 'active',
        ]);

        return back()->with('success', 'Kontrak kerja dibuat.');
    }

    public function terminateContract(EmploymentContract $contract): RedirectResponse
    {
        $this->authorizeOwn($contract);
        $this->service->terminateContract($contract);
        return back()->with('success', 'Kontrak diakhiri.');
    }

    public function storeLeave(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_id'   => 'required|exists:staffs,id',
            'type'       => 'required|in:annual,sick,other',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'days'       => 'required|integer|min:1',
            'reason'     => 'nullable|string',
        ]);

        $this->service->requestLeave($this->schoolId(), (int) $data['staff_id'], $data);

        return back()->with('success', 'Cuti diajukan.');
    }

    public function decideLeave(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $this->authorizeOwn($leave);
        $action = $request->validate(['action' => 'required|in:approve,reject'])['action'];

        $action === 'approve'
            ? $this->service->approveLeave($leave, auth()->id())
            : $this->service->rejectLeave($leave, auth()->id());

        return back()->with('success', 'Cuti diproses.');
    }

    public function storeOvertime(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_id'       => 'required|exists:staffs,id',
            'date'           => 'required|date',
            'hours'          => 'required|numeric|min:0.5',
            'rate_per_hour_rupiah' => 'required|numeric|min:0',
            'note'           => 'nullable|string',
        ]);

        $this->service->logOvertime($this->schoolId(), (int) $data['staff_id'], [
            'date'          => $data['date'],
            'hours'         => $data['hours'],
            'rate_per_hour' => (int) round($data['rate_per_hour_rupiah'] * 100),
            'note'          => $data['note'] ?? null,
        ]);

        return back()->with('success', 'Lembur dicatat.');
    }

    public function approveOvertime(OvertimeRecord $record): RedirectResponse
    {
        $this->authorizeOwn($record);
        $this->service->approveOvertime($record, auth()->id());
        return back()->with('success', 'Lembur disetujui.');
    }
}
