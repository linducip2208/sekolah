<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\DB;

class RoleDashboardService
{
    /** Resolve the primary role for dashboard widgets. */
    public function roleFor($user): string
    {
        $role = $user?->getRoleNames()->first() ?? 'admin';

        return match ($role) {
            'super_admin' => 'principal',
            'accountant'  => 'finance',
            'counselor'   => 'counselor',
            'hr'          => 'hr',
            'teacher'     => 'teacher',
            'homeroom_teacher' => 'teacher',
            default       => $role === 'admin' ? 'principal' : $role,
        };
    }

    /** Return role-specific KPI cards + attention list. */
    public function forRole(int $schoolId, int $userId, string $role): array
    {
        $safe = fn (callable $fn, $default = null) => rescue($fn, $default, false);

        $kpis = match ($role) {
            'principal' => $this->principalKpis($schoolId, $safe),
            'teacher'   => $this->teacherKpis($schoolId, $userId, $safe),
            'finance'   => $this->financeKpis($schoolId, $safe),
            'counselor' => $this->counselorKpis($schoolId, $safe),
            'hr'        => $this->hrKpis($schoolId, $safe),
            default     => $this->principalKpis($schoolId, $safe),
        };

        return ['role' => $role, 'kpis' => $kpis];
    }

    private function principalKpis(int $schoolId, callable $safe): array
    {
        return [
            ['label' => 'Siswa Aktif', 'value' => $safe(fn () => \App\Models\Academic\Student::where('school_id', $schoolId)->where('status', 'active')->count(), 0), 'tone' => 'primary', 'href' => route('admin.students.index')],
            ['label' => 'Kehadiran Hari Ini', 'value' => $safe(fn () => $this->attendancePct($schoolId), null), 'tone' => 'success', 'href' => route('admin.attendance.index')],
            ['label' => 'Rata-rata Nilai', 'value' => $safe(fn () => round(\App\Models\Academic\Mark::where('school_id', $schoolId)->where('total_marks', '>', 0)->get()->avg(fn ($m) => $m->obtained_marks / $m->total_marks * 100), 1), null), 'tone' => 'info', 'href' => route('admin.grades.transcript')],
            ['label' => 'Siswa At-Risk', 'value' => $safe(fn () => $this->atRiskCount($schoolId), 0), 'tone' => 'danger', 'href' => route('admin.analytics.dashboard')],
            ['label' => 'Pendaftar PPDB', 'value' => $safe(fn () => \App\Models\PPDB\PpdbApplication::where('school_id', $schoolId)->count(), 0), 'tone' => 'info', 'href' => route('admin.ppdb.dashboard')],
            ['label' => 'Menunggu Approval', 'value' => $safe(fn () => \App\Models\Workflow\WorkflowRequest::where('school_id', $schoolId)->whereIn('status', ['submitted', 'under_review'])->count(), 0), 'tone' => 'warning', 'href' => route('admin.workflow.index')],
        ];
    }

    private function teacherKpis(int $schoolId, int $userId, callable $safe): array
    {
        $today = now()->dayOfWeekIso;

        return [
            ['label' => 'Kelas Hari Ini', 'value' => $safe(fn () => \App\Models\Academic\TimetableSlot::where('school_id', $schoolId)->where('teacher_id', $userId)->where('day_of_week', $today)->count(), 0), 'tone' => 'primary', 'href' => route('admin.timetable.index')],
            ['label' => 'Tugas', 'value' => $safe(fn () => \App\Models\Academic\Assignment::where('school_id', $schoolId)->count(), 0), 'tone' => 'info', 'href' => route('admin.assignments.index')],
            ['label' => 'Ujian', 'value' => $safe(fn () => \App\Models\Academic\Exam::where('school_id', $schoolId)->count(), 0), 'tone' => 'info', 'href' => route('admin.exams.index')],
            ['label' => 'Jurnal Mengajar', 'value' => $safe(fn () => \App\Models\Academic\TeachingJournal::where('school_id', $schoolId)->where('teacher_id', $userId)->count(), 0), 'tone' => 'success', 'href' => route('admin.teaching-journal.index')],
            ['label' => 'Siswa At-Risk', 'value' => $safe(fn () => $this->atRiskCount($schoolId), 0), 'tone' => 'danger', 'href' => route('admin.analytics.dashboard')],
        ];
    }

    private function financeKpis(int $schoolId, callable $safe): array
    {
        $revenue = $safe(fn () => (int) DB::table('fee_payments as fp')->join('fee_invoices as fi', 'fi.id', '=', 'fp.fee_invoice_id')->where('fi.school_id', $schoolId)->where('fp.payment_date', '>=', now()->startOfMonth())->sum('fp.amount'), 0);
        $outstanding = $safe(fn () => (int) \App\Models\Finance\FeeInvoice::where('school_id', $schoolId)->whereIn('status', ['unpaid', 'partial', 'overdue'])->sum(DB::raw('amount - paid_amount')), 0);
        $totalBilled = $safe(fn () => (int) \App\Models\Finance\FeeInvoice::where('school_id', $schoolId)->sum('paid_amount'), 0);
        $totalAmount = $safe(fn () => (int) \App\Models\Finance\FeeInvoice::where('school_id', $schoolId)->sum('amount'), 0);
        $collectionRate = $totalAmount > 0 ? round($totalBilled / $totalAmount * 100) : null;

        return [
            ['label' => 'Pendapatan Bulan Ini', 'value' => $revenue > 0 ? 'Rp ' . number_format($revenue / 100, 0, ',', '.') : null, 'tone' => 'success', 'href' => route('admin.finance.reports.summary')],
            ['label' => 'Outstanding SPP', 'value' => 'Rp ' . number_format($outstanding / 100, 0, ',', '.'), 'tone' => 'danger', 'href' => route('admin.fee.invoices.index')],
            ['label' => 'Collection Rate', 'value' => $collectionRate !== null ? $collectionRate . '%' : null, 'tone' => 'info', 'href' => route('admin.finance.reports.summary')],
            ['label' => 'Beban (Budget)', 'value' => 'Rp ' . number_format($safe(fn () => (int) DB::table('budget_transactions')->where('school_id', $schoolId)->sum('amount'), 0) / 100, 0, ',', '.'), 'tone' => 'warning', 'href' => route('admin.budget.dashboard')],
            ['label' => 'Slip Gaji', 'value' => $safe(fn () => \App\Models\Finance\SalarySlip::where('school_id', $schoolId)->count(), 0), 'tone' => 'primary', 'href' => route('admin.payroll.slips.index')],
        ];
    }

    private function counselorKpis(int $schoolId, callable $safe): array
    {
        return [
            ['label' => 'Siswa At-Risk', 'value' => $safe(fn () => $this->atRiskCount($schoolId), 0), 'tone' => 'danger', 'href' => route('admin.analytics.dashboard')],
            ['label' => 'Sesi Konseling', 'value' => $safe(fn () => \App\Models\Counseling\CounselingSession::where('school_id', $schoolId)->count(), 0), 'tone' => 'primary', 'href' => route('admin.counseling.sessions.index')],
            ['label' => 'Kasus Disiplin', 'value' => $safe(fn () => \App\Models\Discipline\DisciplineRecord::where('school_id', $schoolId)->count(), 0), 'tone' => 'warning', 'href' => route('admin.discipline.records.index')],
            ['label' => 'Laporan Bullying', 'value' => $safe(fn () => \App\Models\Discipline\BullyingReport::where('school_id', $schoolId)->count(), 0), 'tone' => 'danger', 'href' => route('admin.discipline.records.index')],
        ];
    }

    private function hrKpis(int $schoolId, callable $safe): array
    {
        return [
            ['label' => 'Karyawan', 'value' => $safe(fn () => \App\Models\Academic\Staff::where('school_id', $schoolId)->count(), 0), 'tone' => 'primary', 'href' => route('admin.staff.index')],
            ['label' => 'Cuti Pending', 'value' => $safe(fn () => \App\Models\Hr\LeaveRequest::where('school_id', $schoolId)->where('status', 'pending')->count(), 0), 'tone' => 'warning', 'href' => route('admin.hr.index')],
            ['label' => 'Lembur Pending', 'value' => $safe(fn () => \App\Models\Hr\OvertimeRecord::where('school_id', $schoolId)->where('status', 'pending')->count(), 0), 'tone' => 'warning', 'href' => route('admin.hr.index')],
            ['label' => 'Kontrak Aktif', 'value' => $safe(fn () => \App\Models\Hr\EmploymentContract::where('school_id', $schoolId)->where('status', 'active')->count(), 0), 'tone' => 'success', 'href' => route('admin.hr.index')],
        ];
    }

    private function attendancePct(int $schoolId): ?int
    {
        $att = \App\Models\Academic\Attendance::where('school_id', $schoolId)->whereDate('date', today())->get();
        return $att->count() > 0 ? round($att->whereIn('status', ['present', 'late'])->count() / $att->count() * 100) : null;
    }

    private function atRiskCount(int $schoolId): int
    {
        return \App\Models\Analytics\StudentRiskScore::where('school_id', $schoolId)
            ->orderByDesc('snapshot_date')->get()
            ->unique('student_id')->whereIn('risk_level', ['high', 'critical'])->count();
    }
}
