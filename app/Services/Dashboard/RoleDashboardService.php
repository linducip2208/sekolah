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
            'librarian'   => 'librarian',
            'receptionist' => 'ppdb_officer',
            'nurse'       => 'nurse',
            'transport_admin' => 'transport',
            'hostel_admin' => 'hostel',
            'procurement_admin' => 'procurement',
            'foundation_admin' => 'foundation',
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
            'librarian' => $this->librarianKpis($schoolId, $safe),
            'ppdb_officer' => $this->ppdbOfficerKpis($schoolId, $safe),
            'nurse'     => $this->nurseKpis($schoolId, $safe),
            'transport' => $this->transportKpis($schoolId, $safe),
            'hostel'    => $this->hostelKpis($schoolId, $safe),
            'procurement' => $this->procurementKpis($schoolId, $safe),
            'foundation' => $this->foundationKpis($safe),
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

    /* ================================================================
     * Operational roles — dashboard relevan untuk pekerjaannya masing2
     * ================================================================ */

    private function librarianKpis(int $schoolId, callable $safe): array
    {
        $overdue = $safe(fn () => \App\Models\Facilities\BookIssue::where('school_id', $schoolId)
            ->whereNull('returned_at')->where('due_date', '<', now())->count(), 0);

        return [
            ['label' => 'Koleksi Buku', 'value' => number_format($safe(fn () => \App\Models\Facilities\Book::where('school_id', $schoolId)->sum('stock'), 0)), 'tone' => 'primary', 'href' => rescue(fn () => route('admin.library.books.index'), '#', false)],
            ['label' => 'Sedang Dipinjam', 'value' => number_format($safe(fn () => \App\Models\Facilities\BookIssue::where('school_id', $schoolId)->whereNull('returned_at')->count(), 0)), 'tone' => 'info', 'href' => rescue(fn () => route('admin.library.issues.index'), '#', false)],
            ['label' => 'Terlambat Kembali', 'value' => number_format($overdue), 'tone' => $overdue > 0 ? 'danger' : 'success', 'href' => rescue(fn () => route('admin.library.issues.index'), '#', false)],
            ['label' => 'e-Library', 'value' => number_format($safe(fn () => \App\Models\Facilities\DigitalBookIssue::where('school_id', $schoolId)->count(), 0)), 'tone' => 'accent', 'href' => rescue(fn () => route('admin.library.digital.upload'), '#', false)],
        ];
    }

    private function ppdbOfficerKpis(int $schoolId, callable $safe): array
    {
        $pending = $safe(fn () => \App\Models\PPDB\PpdbApplication::where('school_id', $schoolId)
            ->where('status', 'submitted')->count(), 0);
        $total = $safe(fn () => \App\Models\PPDB\PpdbApplication::where('school_id', $schoolId)->count(), 0);
        $accepted = $safe(fn () => \App\Models\PPDB\PpdbApplication::where('school_id', $schoolId)
            ->where('status', 'accepted')->count(), 0);

        return [
            ['label' => 'Menunggu Verifikasi', 'value' => number_format($pending), 'tone' => $pending > 0 ? 'warning' : 'success', 'href' => rescue(fn () => route('admin.ppdb.applications.index'), '#', false)],
            ['label' => 'Total Pendaftar', 'value' => number_format($total), 'tone' => 'primary', 'href' => rescue(fn () => route('admin.ppdb.applications.index'), '#', false)],
            ['label' => 'Diterima', 'value' => number_format($accepted), 'tone' => 'success', 'href' => rescue(fn () => route('admin.ppdb.dashboard'), '#', false)],
            ['label' => 'Conversion Rate', 'value' => ($total > 0 ? round($accepted / $total * 100) : 0) . '%', 'tone' => 'info', 'href' => rescue(fn () => route('admin.analytics.ppdb'), '#', false)],
            ['label' => 'Dashboard PPDB', 'value' => '→', 'tone' => 'accent', 'href' => rescue(fn () => route('admin.ppdb.dashboard'), '#', false)],
        ];
    }

    private function nurseKpis(int $schoolId, callable $safe): array
    {
        $todayVisits = $safe(fn () => \App\Models\Medical\ClinicVisit::where('school_id', $schoolId)
            ->whereDate('visit_date', today())->count(), 0);
        $followUps = $safe(fn () => \App\Models\Medical\ClinicVisit::where('school_id', $schoolId)
            ->whereDate('visit_date', today())->whereNotNull('follow_up_note')->count(), 0);

        return [
            ['label' => 'Kunjungan UKS Hari Ini', 'value' => number_format($todayVisits), 'tone' => 'primary', 'href' => rescue(fn () => route('admin.clinic.visits.index'), '#', false)],
            ['label' => 'Perlu Tindak Lanjut', 'value' => number_format($followUps), 'tone' => $followUps > 0 ? 'warning' : 'success', 'href' => rescue(fn () => route('admin.clinic.visits.index'), '#', false)],
            ['label' => 'Rekam Medis', 'value' => number_format($safe(fn () => \App\Models\Medical\MedicalRecord::where('school_id', $schoolId)->count(), 0)), 'tone' => 'info', 'href' => rescue(fn () => route('admin.clinic.visits.index'), '#', false)],
            ['label' => 'Imunisasi Tercatat', 'value' => number_format($safe(fn () => \App\Models\Medical\Vaccination::where('school_id', $schoolId)->count(), 0)), 'tone' => 'success', 'href' => rescue(fn () => route('admin.clinic.visits.index'), '#', false)],
        ];
    }

    private function transportKpis(int $schoolId, callable $safe): array
    {
        return [
            ['label' => 'Rute Aktif', 'value' => number_format($safe(fn () => \App\Models\Facilities\TransportRoute::where('school_id', $schoolId)->count(), 0)), 'tone' => 'primary', 'href' => rescue(fn () => route('admin.transport.dashboard'), '#', false)],
            ['label' => 'Armada', 'value' => number_format($safe(fn () => \App\Models\Facilities\Vehicle::where('school_id', $schoolId)->count(), 0)), 'tone' => 'info', 'href' => rescue(fn () => route('admin.transport.dashboard'), '#', false)],
            ['label' => 'Penumpang Terdaftar', 'value' => number_format($safe(fn () => \App\Models\Facilities\StudentTransport::where('school_id', $schoolId)->count(), 0)), 'tone' => 'accent', 'href' => rescue(fn () => route('admin.transport.dashboard'), '#', false)],
            ['label' => 'Absensi Transport Hari Ini', 'value' => number_format($safe(fn () => \App\Models\Transport\TransportAttendance::where('school_id', $schoolId)->whereDate('date', today())->count(), 0)), 'tone' => 'success', 'href' => rescue(fn () => route('admin.transport.attendance.index'), '#', false)],
            ['label' => 'Jadwal Pengemudi', 'value' => number_format($safe(fn () => \App\Models\Transport\DriverSchedule::where('school_id', $schoolId)->whereDate('schedule_date', today())->count(), 0)), 'tone' => 'warning', 'href' => rescue(fn () => route('admin.transport.driver-schedules.index'), '#', false)],
        ];
    }

    private function hostelKpis(int $schoolId, callable $safe): array
    {
        $capacity = (int) $safe(fn () => \App\Models\Facilities\HostelRoom::where('school_id', $schoolId)->sum('capacity'), 0);
        $occupied = (int) $safe(fn () => \App\Models\Facilities\HostelRoom::where('school_id', $schoolId)->sum('occupied'), 0);

        return [
            ['label' => 'Asrama', 'value' => number_format($safe(fn () => \App\Models\Facilities\Hostel::where('school_id', $schoolId)->count(), 0)), 'tone' => 'primary', 'href' => rescue(fn () => route('admin.hostel.list.index'), '#', false)],
            ['label' => 'Okupansi', 'value' => ($capacity > 0 ? round($occupied / $capacity * 100) : 0) . '%', 'hint' => "{$occupied}/{$capacity} tempat tidur", 'tone' => 'info', 'href' => rescue(fn () => route('admin.hostel.list.index'), '#', false)],
            ['label' => 'Absensi Asrama Hari Ini', 'value' => number_format($safe(fn () => \App\Models\Facilities\HostelAttendance::where('school_id', $schoolId)->whereDate('date', today())->count(), 0)), 'tone' => 'success', 'href' => rescue(fn () => route('admin.hostel.attendances.index'), '#', false)],
            ['label' => 'Gate Pass Aktif', 'value' => number_format($safe(fn () => \App\Models\Facilities\HostelGatePass::where('school_id', $schoolId)->whereNull('returned_at')->count(), 0)), 'tone' => 'warning', 'href' => rescue(fn () => route('admin.hostel.gate-passes.index'), '#', false)],
        ];
    }

    private function procurementKpis(int $schoolId, callable $safe): array
    {
        $pending = $safe(fn () => \App\Models\Finance\ProcurementRequest::where('school_id', $schoolId)
            ->where('status', 'pending')->count(), 0);

        return [
            ['label' => 'Menunggu Approval', 'value' => number_format($pending), 'tone' => $pending > 0 ? 'warning' : 'success', 'href' => rescue(fn () => route('admin.procurement.approvals'), '#', false)],
            ['label' => 'Total Permintaan', 'value' => number_format($safe(fn () => \App\Models\Finance\ProcurementRequest::where('school_id', $schoolId)->count(), 0)), 'tone' => 'primary', 'href' => rescue(fn () => route('admin.procurement.index'), '#', false)],
            ['label' => 'Supplier Aktif', 'value' => number_format($safe(fn () => \App\Models\Finance\Supplier::where('school_id', $schoolId)->count(), 0)), 'tone' => 'info', 'href' => rescue(fn () => route('admin.procurement.suppliers'), '#', false)],
            ['label' => 'Aset Perlu Maintenance', 'value' => number_format($safe(fn () => \App\Models\Inventory\MaintenanceRequest::where('school_id', $schoolId)->whereNull('completed_at')->count(), 0)), 'tone' => 'danger', 'href' => rescue(fn () => route('admin.misc.maintenance.index'), '#', false)],
        ];
    }

    private function foundationKpis(callable $safe): array
    {
        return [
            ['label' => 'Dashboard Yayasan', 'value' => '→', 'tone' => 'primary', 'href' => rescue(fn () => route('admin.foundation.dashboard'), '#', false)],
            ['label' => 'Benchmark Sekolah', 'value' => '→', 'tone' => 'info', 'href' => rescue(fn () => route('admin.foundation.benchmark.index'), '#', false)],
            ['label' => 'Master Data', 'value' => '→', 'tone' => 'accent', 'href' => rescue(fn () => route('admin.foundation.master-data.index'), '#', false)],
            ['label' => 'User Management', 'value' => '→', 'tone' => 'success', 'href' => rescue(fn () => route('admin.foundation.user-management.index'), '#', false)],
        ];
    }
}
