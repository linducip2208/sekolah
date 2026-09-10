<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\DB;

/**
 * My Work — satu pusat pekerjaan per pengguna.
 *
 * Mengagregasi semua hal yang butuh aksi dari pengguna saat ini
 * (approval, verifikasi, penilaian, input harian) dengan prioritas
 * dan CTA yang jelas. Semua query tenant-scoped + rescue-guarded agar
 * tidak pernah melempar error ke UI.
 */
class MyWorkService
{
    public const PRIORITIES = ['critical' => 0, 'high' => 1, 'normal' => 2];

    /** Role sets per bucket. */
    protected function isAdminish($user): bool
    {
        return in_array($user?->getRoleNames()->first(), ['admin', 'super_admin', 'principal'], true);
    }

    /**
     * Semua item kerja untuk user. Setiap item:
     * key, label, desc, count, priority(critical|high|normal), href, cta.
     */
    public function items(int $schoolId, $user): array
    {
        $safe = fn (callable $fn, $default = 0) => rescue($fn, $default, false);
        $uid = $user?->id;
        $role = $user?->getRoleNames()->first();
        $items = [];

        // ===== Approvals (workflow) — untuk approver / management =====
        $workflowPending = $safe(fn () => \App\Models\Workflow\WorkflowRequest::where('school_id', $schoolId)
            ->whereIn('status', ['submitted', 'under_review'])
            ->when($uid && !$this->isAdminish($user), fn ($q) => $q->where('approver_id', $uid))
            ->count());
        $workflowOld = $safe(fn () => \App\Models\Workflow\WorkflowRequest::where('school_id', $schoolId)
            ->whereIn('status', ['submitted', 'under_review'])
            ->where('submitted_at', '<=', now()->subDays(3))
            ->count());
        if ($workflowPending > 0) {
            $items[] = [
                'key' => 'workflow',
                'label' => 'Persetujuan menunggu',
                'desc' => $workflowOld > 0
                    ? "{$workflowPending} permintaan menunggu · {$workflowOld} sudah >3 hari"
                    : "{$workflowPending} permintaan menunggu keputusan Anda",
                'count' => $workflowPending,
                'priority' => $workflowOld > 0 ? 'critical' : 'high',
                'href' => route('admin.workflow.index'),
                'cta' => 'Tinjau Sekarang',
            ];
        }

        // ===== Verifikasi PPDB =====
        $ppdbRoles = ['admin', 'super_admin', 'receptionist', 'principal'];
        if (in_array($role, $ppdbRoles, true)) {
            $ppdbQueue = $safe(fn () => \App\Models\PPDB\PpdbApplication::where('school_id', $schoolId)
                ->where('status', 'submitted')->count());
            if ($ppdbQueue > 0) {
                $items[] = [
                    'key' => 'ppdb',
                    'label' => 'Verifikasi pendaftar PPDB',
                    'desc' => "{$ppdbQueue} berkas menunggu verifikasi dokumen",
                    'count' => $ppdbQueue,
                    'priority' => 'high',
                    'href' => route('admin.ppdb.applications.index'),
                    'cta' => 'Verifikasi Berkas',
                ];
            }
        }

        // ===== Tagihan overdue — keluarga keuangan =====
        if (in_array($role, ['admin', 'super_admin', 'accountant'], true)) {
            $overdue = $safe(fn () => \App\Models\Finance\FeeInvoice::where('school_id', $schoolId)
                ->where('status', 'overdue')->count());
            if ($overdue > 0) {
                $items[] = [
                    'key' => 'invoices',
                    'label' => 'Tagihan jatuh tempo',
                    'desc' => "{$overdue} invoice overdue — kirim reminder atau catat pembayaran",
                    'count' => $overdue,
                    'priority' => 'critical',
                    'href' => route('admin.fee.invoices.index', ['status' => 'overdue']),
                    'cta' => 'Lihat & Kirim Reminder',
                ];
            }
        }

        // ===== Approval pengadaan =====
        if (in_array($role, ['admin', 'super_admin', 'procurement_admin'], true)) {
            $procurement = $safe(fn () => \App\Models\Finance\ProcurementRequest::where('school_id', $schoolId)
                ->where('status', 'pending')->count());
            if ($procurement > 0) {
                $items[] = [
                    'key' => 'procurement',
                    'label' => 'Persetujuan pengadaan',
                    'desc' => "{$procurement} permintaan belanja menunggu review",
                    'count' => $procurement,
                    'priority' => 'high',
                    'href' => route('admin.procurement.index'),
                    'cta' => 'Review Pengadaan',
                ];
            }
        }

        // ===== Approval dokumen — ditugaskan ke saya =====
        $docApprovals = $safe(fn () => \App\Models\Communication\DocumentApproval::query()
            ->where('status', 'pending')
            ->when(true, function ($q) use ($schoolId, $uid) {
                $q->whereHas('document', fn ($d) => $d->where('school_id', $schoolId));
                if ($uid) {
                    $q->where(fn ($w) => $w->where('approver_id', $uid)->orWhereNull('approver_id'));
                }
            })->count());
        if ($docApprovals > 0) {
            $items[] = [
                'key' => 'documents',
                'label' => 'Persetujuan dokumen',
                'desc' => "{$docApprovals} dokumen menunggu persetujuan",
                'count' => $docApprovals,
                'priority' => 'normal',
                'href' => route('admin.documents.approvals'),
                'cta' => 'Buka Approval',
            ];
        }

        // ===== Cuti pending — HR =====
        if (in_array($role, ['admin', 'super_admin', 'hr'], true)) {
            $leave = $safe(fn () => \App\Models\Hr\LeaveRequest::where('school_id', $schoolId)
                ->where('status', 'pending')->count());
            if ($leave > 0) {
                $items[] = [
                    'key' => 'leave',
                    'label' => 'Pengajuan cuti',
                    'desc' => "{$leave} pengajuan cuti pegawai menunggu keputusan",
                    'count' => $leave,
                    'priority' => 'normal',
                    'href' => route('admin.hr.index'),
                    'cta' => 'Proses Cuti',
                ];
            }
        }

        // ===== Guru: jurnal mengajar belum diisi hari ini =====
        if (in_array($role, ['teacher', 'homeroom_teacher'], true) && $uid) {
            $journalDone = $safe(fn () => \App\Models\Academic\TeachingJournal::where('school_id', $schoolId)
                ->where('teacher_id', $uid)->whereDate('created_at', today())->exists());
            if (!$journalDone) {
                $items[] = [
                    'key' => 'journal',
                    'label' => 'Jurnal mengajar belum diisi',
                    'desc' => 'Catat pembelajaran hari ini sebelum lupa',
                    'count' => 1,
                    'priority' => 'high',
                    'href' => route('admin.teaching-journal.index'),
                    'cta' => 'Isi Jurnal',
                ];
            }
        }

        // ===== Notifikasi belum dibaca =====
        $unread = $safe(fn () => $user ? $user->unreadNotifications()->count() : 0);
        if ($unread > 0) {
            $items[] = [
                'key' => 'notifications',
                'label' => 'Notifikasi belum dibaca',
                'desc' => "{$unread} notifikasi menunggu dibaca",
                'count' => $unread,
                'priority' => 'normal',
                'href' => route('admin.notifications.index'),
                'cta' => 'Buka Notifikasi',
            ];
        }

        return collect($items)
            ->sortBy(fn ($i) => self::PRIORITIES[$i['priority']] ?? 3)
            ->values()
            ->all();
    }

    /** Jumlah total item (untuk badge sidebar/topbar). */
    public function totalCount(int $schoolId, $user): int
    {
        return (int) collect($this->items($schoolId, $user))->sum('count');
    }
}
