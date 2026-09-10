<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * DashboardDataService — command center data untuk dashboard admin.
 *
 * Semua query dashboard hidup di sini (bukan di Blade), tenant-scoped,
 * rescue-guarded, dan di-cache singkat (120 detik) agar refresh berulang
 * tidak memicu puluhan query berat.
 */
class DashboardDataService
{
    protected int $ttl = 120;

    protected RoleDashboardService $roles;

    public function __construct(RoleDashboardService $roles)
    {
        $this->roles = $roles;
    }

    /**
     * Seluruh payload dashboard: context, kpis, alerts, charts, lists.
     */
    public function for($user): array
    {
        $schoolId = (int) $user->school_id;
        $userId = (int) $user->id;
        $roleKey = $this->roles->roleFor($user);

        return Cache::remember(
            "dash:v2:{$schoolId}:{$roleKey}:{$userId}",
            $this->ttl,
            function () use ($user, $schoolId, $roleKey) {
                return [
                    'context' => $this->context($schoolId),
                    'kpis' => $this->kpis($user, $schoolId, $roleKey),
                    'alerts' => $this->alerts($schoolId, $roleKey),
                    'charts' => $this->charts($schoolId, $roleKey),
                    'lists' => $this->lists($user, $schoolId, $roleKey),
                    'setup' => rescue(
                        fn () => app(SchoolSetupService::class)->progress($schoolId),
                        ['percent' => 100, 'done' => 1, 'total' => 1, 'steps' => []],
                        false
                    ),
                ];
            }
        );
    }

    public static function flush(int $schoolId): void
    {
        try {
            Cache::forget("dash:{$schoolId}:*");
        } catch (\Throwable) {
        }
    }

    /* ================================================================
     * Context — greeting, sekolah, tahun ajaran & semester aktif
     * ================================================================ */
    protected function context(int $schoolId): array
    {
        $hour = (int) now()->format('G');
        $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));

        [$yearName, $semesterName] = rescue(function () use ($schoolId) {
            $year = \App\Models\Academic\AcademicYear::where('school_id', $schoolId)->where('is_active', true)->first();
            if (!$year) {
                return ['—', null];
            }
            $semester = \App\Models\Academic\Semester::where('academic_year_id', $year->id)->where('is_active', true)->first();

            return [$year->name, $semester?->name];
        }, ['—', null], false);

        return [
            'greeting' => $greeting,
            'date' => now()->translatedFormat('l, d F Y'),
            'school_name' => rescue(fn () => \App\Models\School::where('id', $schoolId)->value('name'), null, false),
            'academic_year' => $yearName,
            'semester' => $semesterName,
        ];
    }

    /* ================================================================
     * KPI — maksimal 6 kartu per role (satu baris, tanpa duplikasi)
     * ================================================================ */
    protected function kpis($user, int $schoolId, string $roleKey): array
    {
        // RoleDashboardService sudah menyediakan KPI utama per role.
        $kpis = $this->roles->forRole($schoolId, $user->id, $roleKey)['kpis'];

        // Tambah konteks dasar jika role tertentu kurang dari 4 kartu.
        if (count($kpis) < 4) {
            $students = rescue(fn () => \App\Models\Academic\Student::where('school_id', $schoolId)->count(), 0, false);
            array_unshift($kpis, [
                'label' => 'Total Siswa',
                'value' => number_format((float) $students, 0, ',', '.'),
                'tone' => 'primary',
                'href' => route('admin.students.index'),
            ]);
        }

        return array_slice($kpis, 0, 6);
    }

    /* ================================================================
     * Alerts — actionable, setiap item punya CTA eksplisit
     * ================================================================ */
    protected function alerts(int $schoolId, string $roleKey): array
    {
        $safe = fn (callable $fn, $default = 0) => rescue($fn, $default, false);
        $alerts = [];

        $overdueInvoices = $safe(fn () => \App\Models\Finance\FeeInvoice::where('school_id', $schoolId)->where('status', 'overdue')->count());
        if ($overdueInvoices > 0 && in_array($roleKey, ['principal', 'finance'], true)) {
            $alerts[] = [
                'icon' => 'money', 'tone' => 'danger',
                'title' => "{$overdueInvoices} tagihan overdue",
                'desc' => 'Kirim reminder atau catat pembayaran sebelum piutang membengkak.',
                'count' => $overdueInvoices,
                'cta' => 'Lihat & Kirim Reminder',
                'href' => route('admin.fee.invoices.index', ['status' => 'overdue']),
            ];
        }

        $pendingWorkflow = $safe(fn () => \App\Models\Workflow\WorkflowRequest::where('school_id', $schoolId)
            ->whereIn('status', ['submitted', 'under_review'])->count());
        $pendingProcurement = in_array($roleKey, ['principal', 'finance'], true)
            ? $safe(fn () => \App\Models\Finance\ProcurementRequest::where('school_id', $schoolId)->where('status', 'pending')->count())
            : 0;
        $approvals = $pendingWorkflow + $pendingProcurement;
        if ($approvals > 0) {
            $alerts[] = [
                'icon' => 'alert', 'tone' => $approvals > 10 ? 'danger' : 'warning',
                'title' => "{$approvals} permintaan menunggu persetujuan",
                'desc' => 'Keputusan Anda menentukan lanjut proses berikutnya.',
                'count' => $approvals,
                'cta' => 'Tinjau Persetujuan',
                'href' => route('admin.my-work'),
            ];
        }

        $atRiskCount = $safe(function () use ($schoolId) {
            return \App\Models\Analytics\StudentRiskScore::where('school_id', $schoolId)
                ->orderByDesc('snapshot_date')->get()
                ->unique('student_id')->whereIn('risk_level', ['high', 'critical'])->count();
        });
        if ($atRiskCount > 0) {
            $alerts[] = [
                'icon' => 'users', 'tone' => 'danger',
                'title' => "{$atRiskCount} siswa at-risk",
                'desc' => 'Skor risiko tinggi — butuh intervensi wali kelas / BK.',
                'count' => $atRiskCount,
                'cta' => 'Buka Student Risk',
                'href' => route('admin.analytics.risks.index'),
            ];
        }

        $absentToday = $safe(fn () => \App\Models\Academic\Attendance::where('school_id', $schoolId)
            ->whereDate('date', today())->whereIn('status', ['absent'])->count());
        if ($absentToday > 0 && in_array($roleKey, ['principal', 'teacher', 'finance'], true)) {
            $alerts[] = [
                'icon' => 'clock', 'tone' => 'warning',
                'title' => "{$absentToday} siswa tercatat alpha hari ini",
                'desc' => 'Periksa pola absensi dan follow up ke orang tua bila perlu.',
                'count' => $absentToday,
                'cta' => 'Cek Absensi',
                'href' => route('admin.attendance.index'),
            ];
        }

        $ppdbPending = $safe(fn () => \App\Models\PPDB\PpdbApplication::where('school_id', $schoolId)
            ->where('status', 'submitted')->count());
        if ($ppdbPending > 0) {
            $alerts[] = [
                'icon' => 'admissions', 'tone' => 'info',
                'title' => "{$ppdbPending} pendaftar PPDB belum diverifikasi",
                'desc' => 'Berkas menunggu review dokumen agar calon siswa bisa lanjut seleksi.',
                'count' => $ppdbPending,
                'cta' => 'Verifikasi Berkas',
                'href' => route('admin.ppdb.applications.index'),
            ];
        }

        return array_slice(array_values(array_filter($alerts)), 0, 5);
    }

    /* ================================================================
     * Charts — kehadiran doughnut + tren keuangan 6 bulan
     * ================================================================ */
    protected function charts(int $schoolId, string $roleKey): array
    {
        $todayAtt = rescue(fn () => \App\Models\Academic\Attendance::where('school_id', $schoolId)
            ->whereDate('date', today())->get(), collect(), false);

        $breakdown = [
            'present' => $todayAtt->where('status', 'present')->count(),
            'late' => $todayAtt->where('status', 'late')->count(),
            'absent' => $todayAtt->where('status', 'absent')->count(),
            'other' => $todayAtt->whereIn('status', ['half_day', 'on_leave'])->count(),
        ];
        $attTotal = $todayAtt->count();
        $attPresent = $breakdown['present'] + $breakdown['late'];

        $months = [];
        $revenue = [];
        $expense = [];
        $hasFinance = false;

        if (in_array($roleKey, ['principal', 'finance'], true)) {
            $revByMonth = rescue(fn () => DB::table('fee_payments as fp')
                ->join('fee_invoices as fi', 'fi.id', '=', 'fp.fee_invoice_id')
                ->where('fi.school_id', $schoolId)
                ->where('fp.payment_date', '>=', now()->subMonths(5)->startOfMonth())
                ->selectRaw("DATE_FORMAT(fp.payment_date, '%Y-%m') as m, SUM(fp.amount) as total")
                ->groupBy('m')->pluck('total', 'm'), collect(), false);
            $expByMonth = rescue(fn () => DB::table('budget_transactions')
                ->where('school_id', $schoolId)
                ->where('transaction_date', '>=', now()->subMonths(5)->startOfMonth())
                ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') as m, SUM(amount) as total")
                ->groupBy('m')->pluck('total', 'm'), collect(), false);

            for ($i = 5; $i >= 0; $i--) {
                $m = now()->subMonths($i);
                $months[] = $m->translatedFormat('M');
                $r = (int) ($revByMonth[$m->format('Y-m')] ?? 0);
                $e = (int) ($expByMonth[$m->format('Y-m')] ?? 0);
                $revenue[] = $r;
                $expense[] = $e;
                if ($r > 0 || $e > 0) {
                    $hasFinance = true;
                }
            }
        }

        return [
            'attendance' => [
                'total' => $attTotal,
                'present_pct' => $attTotal > 0 ? round($attPresent / $attTotal * 100) : null,
                'breakdown' => $breakdown,
            ],
            'finance' => [
                'months' => $months,
                'revenue' => $revenue,
                'expense' => $expense,
                'enabled' => $hasFinance,
            ],
        ];
    }

    /* ================================================================
     * Lists — at-risk, my work preview, agenda, aktivitas
     * ================================================================ */
    protected function lists($user, int $schoolId, string $roleKey): array
    {
        $safe = function (callable $fn, $default = null) {
            return rescue($fn, $default ?? collect(), false);
        };

        // PENTING: jangan cache objek Eloquent — konversi ke plain array
        // agar aman di-serialize (menghindari __PHP_Incomplete_Class).
        $atRisk = in_array($roleKey, ['principal'], true)
            ? $safe(function () use ($schoolId) {
                return \App\Models\Analytics\StudentRiskScore::where('school_id', $schoolId)
                    ->with('student.user:id,name')->orderByDesc('snapshot_date')->get()
                    ->unique('student_id')->whereIn('risk_level', ['high', 'critical'])
                    ->sortByDesc('overall_risk')->take(5)
                    ->map(fn ($r) => [
                        'student_id' => $r->student_id,
                        'name' => $r->student?->user?->name ?? ('Siswa #' . $r->student_id),
                        'level' => $r->risk_level,
                        'score' => $r->overall_risk,
                    ])->values()->all();
            }, [])
            : [];

        $myWork = app(MyWorkService::class)->items($schoolId, $user);
        $myWorkPreview = array_slice($myWork, 0, 4);
        $myWorkTotal = (int) collect($myWork)->sum('count');

        $events = $safe(fn () => \App\Models\Academic\CalendarEvent::where('school_id', $schoolId)
            ->where('is_published', true)->whereDate('start_date', '>=', today())
            ->orderBy('start_date')->limit(5)->get()
            ->map(fn ($e) => [
                'title' => $e->title,
                'type' => ucfirst((string) $e->event_type),
                'day' => $e->start_date?->format('d'),
                'month' => $e->start_date?->translatedFormat('M'),
            ])->all(), []);

        // Tenant-safe: hanya aktivitas yang causer-nya user sekolah ini.
        $activity = $safe(function () use ($schoolId) {
            return \Spatie\Activitylog\Models\Activity::query()
                ->whereHasMorph('causer', [\App\Models\User::class], fn ($q) => $q->where('school_id', $schoolId))
                ->with('causer:id,name')->latest()->limit(8)->get()
                ->map(fn ($act) => [
                    'actor' => $act->causer?->name ?? 'Sistem',
                    'description' => Str::ucfirst((string) $act->description) . ' ' . class_basename($act->subject_type),
                    'ago' => $act->created_at?->diffForHumans(),
                ])->all();
        }, []);

        return [
            'at_risk' => is_array($atRisk) ? $atRisk : [],
            'my_work' => $myWorkPreview,
            'my_work_total' => $myWorkTotal,
            'events' => is_array($events) ? $events : [],
            'activity' => is_array($activity) ? $activity : [],
        ];
    }
}
