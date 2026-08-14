@extends('layouts.school-admin')
@section('title', 'Dashboard')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

@php
    use Illuminate\Support\Facades\DB;
    $sid = auth()->user()->school_id;
    $school = \App\Models\School::find($sid);
    $safe = function (callable $fn, $default = null) { try { return $fn(); } catch (\Throwable $e) { return $default; } };

    $role = auth()->user()->getRoleNames()->first() ?? 'admin';
    $isAdmin = in_array($role, ['admin', 'super_admin'], true);

    $hour = (int) now()->format('G');
    $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));

    // ===== KPI (real counts only) =====
    $students = $safe(fn () => \App\Models\Academic\Student::where('school_id', $sid)->count());
    $staff    = $safe(fn () => \App\Models\Academic\Staff::where('school_id', $sid)->count());

    $todayAtt = $safe(fn () => \App\Models\Academic\Attendance::where('school_id', $sid)->whereDate('date', today())->get(), collect());
    $attTotal = $todayAtt->count();
    $attPresent = $todayAtt->whereIn('status', ['present', 'late'])->count();
    $attPct = $attTotal > 0 ? round($attPresent / $attTotal * 100) : null;
    $attBreakdown = [
        'present' => $todayAtt->where('status', 'present')->count(),
        'absent'  => $todayAtt->where('status', 'absent')->count(),
        'late'    => $todayAtt->where('status', 'late')->count(),
        'other'   => $todayAtt->whereIn('status', ['half_day', 'on_leave'])->count(),
    ];

    $revenueMonth = $safe(fn () => (int) DB::table('fee_payments as fp')
        ->join('fee_invoices as fi', 'fi.id', '=', 'fp.fee_invoice_id')
        ->where('fi.school_id', $sid)
        ->where('fp.payment_date', '>=', now()->startOfMonth())
        ->sum('fp.amount'), 0);

    $outstanding = $safe(fn () => (int) \App\Models\Finance\FeeInvoice::where('school_id', $sid)
        ->whereIn('status', ['unpaid', 'partial', 'overdue'])
        ->sum(DB::raw('amount - paid_amount')), 0);

    // ===== Needs Attention (real counts) =====
    $overdueInvoices = $safe(fn () => \App\Models\Finance\FeeInvoice::where('school_id', $sid)->where('status', 'overdue')->count());
    $pendingWorkflow = $safe(fn () => \App\Models\Workflow\WorkflowRequest::where('school_id', $sid)->whereIn('status', ['submitted', 'under_review'])->count());
    $pendingProcurement = $safe(fn () => \App\Models\Finance\ProcurementRequest::where('school_id', $sid)->where('status', 'pending')->count());
    $atRiskCount = $safe(function () use ($sid) {
        return \App\Models\Analytics\StudentRiskScore::where('school_id', $sid)->orderByDesc('snapshot_date')->get()
            ->unique('student_id')->whereIn('risk_level', ['high', 'critical'])->count();
    });

    $alerts = array_values(array_filter([
        ['icon' => 'money', 'tone' => 'danger', 'title' => 'Tagihan terlambat', 'desc' => $overdueInvoices . ' invoice melewati jatuh tempo', 'count' => $overdueInvoices, 'href' => route('admin.fee.invoices.index')],
        ['icon' => 'alert', 'tone' => 'warning', 'title' => 'Menunggu persetujuan', 'desc' => ($pendingWorkflow + $pendingProcurement) . ' permintaan perlu ditinjau', 'count' => $pendingWorkflow + $pendingProcurement, 'href' => route('admin.workflow.index')],
        ['icon' => 'users', 'tone' => 'danger', 'title' => 'Siswa at-risk', 'desc' => $atRiskCount . ' siswa perlu intervensi', 'count' => $atRiskCount, 'href' => route('admin.analytics.dashboard')],
        ['icon' => 'clock', 'tone' => 'warning', 'title' => 'Absen hari ini', 'desc' => $attBreakdown['absent'] . ' siswa tercatat alpha', 'count' => $attBreakdown['absent'], 'href' => route('admin.attendance.index')],
    ], fn ($a) => $a['count'] > 0));

    // ===== Charts =====
    $revenueByMonth = $safe(fn () => DB::table('fee_payments as fp')
        ->join('fee_invoices as fi', 'fi.id', '=', 'fp.fee_invoice_id')
        ->where('fi.school_id', $sid)
        ->where('fp.payment_date', '>=', now()->subMonths(5)->startOfMonth())
        ->selectRaw("DATE_FORMAT(fp.payment_date, '%Y-%m') as m, SUM(fp.amount) as total")
        ->groupBy('m')->pluck('total', 'm'), collect());
    $expenseByMonth = $safe(fn () => DB::table('budget_transactions')
        ->where('school_id', $sid)
        ->where('transaction_date', '>=', now()->subMonths(5)->startOfMonth())
        ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') as m, SUM(amount) as total")
        ->groupBy('m')->pluck('total', 'm'), collect());

    $chartMonths = []; $revenueData = []; $expenseData = [];
    for ($i = 5; $i >= 0; $i--) {
        $m = now()->subMonths($i);
        $chartMonths[] = $m->translatedFormat('M');
        $revenueData[] = (int) ($revenueByMonth[$m->format('Y-m')] ?? 0);
        $expenseData[] = (int) ($expenseByMonth[$m->format('Y-m')] ?? 0);
    }
    $hasFinanceChart = array_sum($revenueData) > 0 || array_sum($expenseData) > 0;
    $hasAttendanceChart = $attTotal > 0;

    // ===== Student insights =====
    $atRiskStudents = $safe(function () use ($sid) {
        return \App\Models\Analytics\StudentRiskScore::where('school_id', $sid)
            ->with('student.user:id,name')->orderByDesc('snapshot_date')->get()
            ->unique('student_id')->whereIn('risk_level', ['high', 'critical'])
            ->sortByDesc('overall_risk')->take(5)->values();
    }, collect());

    // ===== My Tasks =====
    $tasks = [
        ['label' => 'Tagihan belum lunas', 'count' => $safe(fn () => \App\Models\Finance\FeeInvoice::where('school_id', $sid)->whereIn('status', ['unpaid', 'partial', 'overdue'])->count()), 'href' => route('admin.fee.invoices.index'), 'tone' => 'warning'],
        ['label' => 'Pendaftar PPDB', 'count' => $safe(fn () => \App\Models\PPDB\PpdbApplication::where('school_id', $sid)->count()), 'href' => route('admin.ppdb.applications.index'), 'tone' => 'warning'],
        ['label' => 'Persetujuan workflow', 'count' => $pendingWorkflow, 'href' => route('admin.workflow.index'), 'tone' => 'warning'],
        ['label' => 'Permintaan pengadaan', 'count' => $pendingProcurement, 'href' => route('admin.procurement.index'), 'tone' => 'warning'],
        ['label' => 'Persetujuan dokumen', 'count' => $safe(fn () => \App\Models\Communication\DocumentApproval::where('school_id', $sid)->count()), 'href' => route('admin.documents.approvals'), 'tone' => 'info'],
    ];
    $tasks = array_values(array_filter($tasks, fn ($t) => $t['count'] > 0));

    // ===== Calendar =====
    $events = $safe(fn () => \App\Models\Academic\CalendarEvent::where('school_id', $sid)
        ->where('is_published', true)->whereDate('start_date', '>=', today())
        ->orderBy('start_date')->limit(5)->get(), collect());

    // ===== Recent activity =====
    $activity = $safe(fn () => \Spatie\Activitylog\Models\Activity::with('causer:id,name')->latest()->limit(8)->get(), collect());
@endphp

<div class="space-y-6">

    {{-- Greeting --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div>
            <div class="text-sm text-[var(--color-text-muted)]">{{ now()->translatedFormat('l, d F Y') }}</div>
            <h1 class="page-title mt-1">{{ $greeting }}, {{ Str::before(auth()->user()->name, ' ') }}.</h1>
            <p class="text-sm text-[var(--color-text-secondary)] mt-1">
                Berikut ringkasan hari ini untuk <strong>{{ $school?->name ?? $platform['app_name'] }}</strong>.
            </p>
        </div>
    </div>

    {{-- KPI overview --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @php
            $kpis = $isAdmin
                ? [
                    ['icon' => 'users', 'label' => 'Siswa', 'value' => number_format($students, 0, ',', '.'), 'sub' => 'total terdaftar'],
                    ['icon' => 'school', 'label' => 'Staf & Guru', 'value' => number_format($staff, 0, ',', '.'), 'sub' => 'total'],
                    ['icon' => 'check', 'label' => 'Kehadiran Hari Ini', 'value' => $attPct !== null ? $attPct.'%' : '—', 'sub' => $attPresent.' dari '.$attTotal.' siswa'],
                    ['icon' => 'money', 'label' => 'Penerimaan Bulan Ini', 'value' => $revenueMonth > 0 ? money($revenueMonth, $school) : '—', 'sub' => 'pembayaran masuk'],
                  ]
                : [
                    ['icon' => 'money', 'label' => 'Penerimaan Bulan Ini', 'value' => $revenueMonth > 0 ? money($revenueMonth, $school) : '—', 'sub' => 'pembayaran masuk'],
                    ['icon' => 'inbox', 'label' => 'Piutang Belum Lunas', 'value' => money($outstanding, $school), 'sub' => 'total tagihan'],
                    ['icon' => 'alert', 'label' => 'Tagihan Terlambat', 'value' => number_format($overdueInvoices, 0, ',', '.'), 'sub' => 'melewati jatuh tempo'],
                    ['icon' => 'clock', 'label' => 'Menunggu Persetujuan', 'value' => number_format($pendingWorkflow + $pendingProcurement, 0, ',', '.'), 'sub' => 'perlu ditinjau'],
                  ];
        @endphp
        @foreach($kpis as $k)
            <div class="card card-pad">
                <div class="flex items-start justify-between">
                    <div class="h-9 w-9 rounded-lg flex items-center justify-center" style="background: var(--color-primary-soft); color: var(--color-primary);">
                        <x-ui.icon :name="$k['icon']" class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3 text-[13px] text-[var(--color-text-secondary)]">{{ $k['label'] }}</div>
                <div class="text-2xl font-extrabold tracking-tight tabular-nums" style="color: var(--color-text);">{{ $k['value'] }}</div>
                <div class="text-xs text-[var(--color-text-muted)] mt-0.5">{{ $k['sub'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Needs Attention + Quick Actions --}}
    <div class="grid lg:grid-cols-12 gap-4">
        <div class="card lg:col-span-8">
            <div class="flex items-center justify-between px-5 py-4 border-b border-[var(--color-border)]">
                <h2 class="section-title">Perlu Perhatian</h2>
                @if($alerts)<span class="badge badge-danger">{{ count($alerts) }}</span>@endif
            </div>
            @if(empty($alerts))
                <div class="px-5 py-6 text-center">
                    <x-ui.icon name="check" class="w-8 h-8 mx-auto text-[var(--color-success)]" />
                    <p class="text-sm font-semibold mt-2">Semua beres.</p>
                    <p class="text-sm text-[var(--color-text-muted)]">Tidak ada hal yang butuh perhatian.</p>
                </div>
            @else
                <div class="divide-y divide-[var(--color-border)]">
                    @foreach($alerts as $a)
                        <a href="{{ $a['href'] }}" class="flex items-center gap-3 px-5 py-3 hover:bg-[var(--color-surface-hover)] transition">
                            <div class="h-9 w-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background: var(--color-{{ $a['tone'] }}-soft); color: var(--color-{{ $a['tone'] }});">
                                <x-ui.icon :name="$a['icon']" class="w-5 h-5" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold">{{ $a['title'] }}</div>
                                <div class="text-xs text-[var(--color-text-muted)]">{{ $a['desc'] }}</div>
                            </div>
                            <span class="badge badge-{{ $a['tone'] }}">{{ $a['count'] }}</span>
                            <span class="text-[var(--color-text-muted)]">→</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card card-pad lg:col-span-4">
            <h2 class="section-title mb-4">Aksi Cepat</h2>
            <div class="grid grid-cols-2 gap-2">
                @php
                    $quickActions = $isAdmin
                        ? [
                            ['admin.students.create', 'Tambah Siswa', 'plus'],
                            ['admin.attendance.index', 'Catat Absensi', 'check'],
                            ['admin.notices.create', 'Pengumuman', 'bell'],
                            ['admin.fee.invoices.index', 'Kelola Tagihan', 'money'],
                            ['admin.workflow.create', 'Ajukan Permintaan', 'edit'],
                            ['admin.events.index', 'Buat Event', 'calendar'],
                          ]
                        : [
                            ['admin.fee.invoices.index', 'Kelola Tagihan', 'money'],
                            ['admin.payroll.slips.index', 'Slip Gaji', 'users'],
                            ['admin.finance.reports.summary', 'Ringkasan Keuangan', 'chart'],
                            ['admin.reports.builder.index', 'Buat Laporan', 'chart'],
                            ['admin.workflow.index', 'Persetujuan', 'alert'],
                            ['admin.procurement.index', 'Pengadaan', 'inbox'],
                          ];
                @endphp
                @foreach($quickActions as $qa)
                    <a href="{{ route($qa[0]) }}" class="flex flex-col items-center gap-1.5 p-3 rounded-lg border border-[var(--color-border)] hover:border-[var(--color-primary)] hover:bg-[var(--color-primary-soft)] transition">
                        <x-ui.icon :name="$qa[2]" class="w-5 h-5 text-[var(--color-primary)]" />
                        <span class="text-xs font-medium text-center">{{ $qa[1] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid lg:grid-cols-12 gap-4">
        {{-- Attendance --}}
        <div class="card card-pad lg:col-span-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="section-title">Kehadiran Hari Ini</h2>
                <a href="{{ route('admin.attendance.index') }}" class="text-sm text-[var(--color-primary)] hover:underline">Lihat</a>
            </div>
            @if(!$hasAttendanceChart)
                <div class="py-8 text-center text-sm text-[var(--color-text-muted)]">Belum ada data absensi hari ini.</div>
            @else
                <div class="h-56"><canvas id="attendanceChart" role="img" aria-label="Ringkasan kehadiran hari ini"></canvas></div>
                <ul class="mt-3 space-y-1.5 text-sm">
                    @foreach([['present' => 'Hadir', 'success'], ['late' => 'Terlambat', 'info'], ['absent' => 'Alpha', 'danger'], ['other' => 'Izin / Setengah Hari', 'warning']] as [$st, $label, $tone])
                        <li class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full" style="background: var(--color-{{ $tone }});"></span>
                            <span class="text-[var(--color-text-secondary)] flex-1">{{ $label }}</span>
                            <span class="font-semibold">{{ $attBreakdown[$st] }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Finance --}}
        <div class="card card-pad lg:col-span-7">
            <div class="flex items-center justify-between mb-3">
                <h2 class="section-title">Penerimaan vs Pengeluaran</h2>
                <a href="{{ route('admin.finance.reports.summary') }}" class="text-sm text-[var(--color-primary)] hover:underline">Detail</a>
            </div>
            @if(!$hasFinanceChart)
                <div class="py-8 text-center text-sm text-[var(--color-text-muted)]">Belum ada data keuangan 6 bulan terakhir.</div>
            @else
                <div class="h-56"><canvas id="financeChart" role="img" aria-label="Tren penerimaan dan pengeluaran 6 bulan terakhir"></canvas></div>
            @endif
        </div>
    </div>

    {{-- Student insights + Tasks --}}
    @if($isAdmin)
    <div class="grid lg:grid-cols-12 gap-4">
        <div class="card lg:col-span-5">
            <div class="flex items-center justify-between px-5 py-4 border-b border-[var(--color-border)]">
                <h2 class="section-title">Siswa At-Risk</h2>
                <a href="{{ route('admin.analytics.dashboard') }}" class="text-sm text-[var(--color-primary)] hover:underline">Semua</a>
            </div>
            @if($atRiskStudents->isEmpty())
                <div class="px-5 py-6 text-center text-sm text-[var(--color-text-muted)]">Tidak ada siswa at-risk pada snapshot terakhir.</div>
            @else
                <ul class="divide-y divide-[var(--color-border)]">
                    @foreach($atRiskStudents as $r)
                        <li class="flex items-center justify-between px-5 py-3">
                            <a href="{{ route('admin.students.show', $r->student_id) }}" class="font-medium text-sm hover:underline">{{ $r->student?->user?->name ?? 'Siswa #'.$r->student_id }}</a>
                            <x-ui.badge :variant="$r->risk_level === 'critical' ? 'danger' : 'warning'">{{ $r->risk_level }} · {{ $r->overall_risk }}</x-ui.badge>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="card lg:col-span-7">
            <div class="flex items-center justify-between px-5 py-4 border-b border-[var(--color-border)]">
                <h2 class="section-title">Tugas Saya</h2>
                @if(!empty($tasks))<span class="badge badge-warning">{{ count($tasks) }}</span>@endif
            </div>
            @if(empty($tasks))
                <div class="px-5 py-6 text-center text-sm text-[var(--color-text-muted)]">Semua tugas selesai. Tidak ada yang menunggu.</div>
            @else
                <ul class="divide-y divide-[var(--color-border)]">
                    @foreach($tasks as $t)
                        <li>
                            <a href="{{ $t['href'] }}" class="flex items-center justify-between px-5 py-3 hover:bg-[var(--color-surface-hover)] transition">
                                <span class="text-sm font-medium">{{ $t['label'] }}</span>
                                <span class="flex items-center gap-2">
                                    <span class="badge badge-{{ $t['tone'] }}">{{ $t['count'] }}</span>
                                    <span class="text-[var(--color-text-muted)]">→</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
    @endif

    {{-- Calendar + Activity --}}
    <div class="grid lg:grid-cols-12 gap-4">
        <div class="card lg:col-span-5">
            <div class="flex items-center justify-between px-5 py-4 border-b border-[var(--color-border)]">
                <h2 class="section-title">Agenda Mendatang</h2>
                <a href="{{ route('admin.calendar.index') }}" class="text-sm text-[var(--color-primary)] hover:underline">Kalender</a>
            </div>
            @if($events->isEmpty())
                <div class="px-5 py-6 text-center text-sm text-[var(--color-text-muted)]">Tidak ada event mendatang.</div>
            @else
                <ul class="divide-y divide-[var(--color-border)]">
                    @foreach($events as $e)
                        <li class="flex items-center gap-3 px-5 py-3">
                            <div class="text-center leading-tight flex-shrink-0 w-11">
                                <div class="text-lg font-extrabold">{{ $e->start_date->format('d') }}</div>
                                <div class="text-[10px] uppercase text-[var(--color-text-muted)]">{{ $e->start_date->translatedFormat('M') }}</div>
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-medium truncate">{{ $e->title }}</div>
                                <div class="text-xs text-[var(--color-text-muted)]">{{ ucfirst($e->event_type) }}</div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="card lg:col-span-7">
            <div class="flex items-center justify-between px-5 py-4 border-b border-[var(--color-border)]">
                <h2 class="section-title">Aktivitas Terbaru</h2>
            </div>
            @if($activity->isEmpty())
                <div class="px-5 py-6 text-center text-sm text-[var(--color-text-muted)]">Belum ada aktivitas tercatat.</div>
            @else
                <ol class="divide-y divide-[var(--color-border)]">
                    @foreach($activity as $act)
                        <li class="flex items-center gap-3 px-5 py-3">
                            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background: var(--color-primary);"></span>
                            <div class="flex-1 min-w-0 text-sm">
                                <span class="font-medium">{{ $act->causer?->name ?? 'Sistem' }}</span>
                                <span class="text-[var(--color-text-secondary)]">{{ Str::ucfirst($act->description) }} {{ class_basename($act->subject_type) }}</span>
                            </div>
                            <span class="text-xs text-[var(--color-text-muted)] whitespace-nowrap">{{ $act->created_at?->diffForHumans() }}</span>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </div>

</div>

@push('scripts')
@if($hasAttendanceChart || $hasFinanceChart)
<script>
(function () {
    if (typeof Chart === 'undefined') return;

    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = getComputedStyle(document.documentElement).getPropertyValue('--color-text-muted').trim() || '#66736F';

    @if($hasAttendanceChart)
    var att = document.getElementById('attendanceChart');
    if (att) {
        new Chart(att, {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Terlambat', 'Alpha', 'Izin / Setengah Hari'],
                datasets: [{
                    data: [{{ $attBreakdown['present'] }}, {{ $attBreakdown['late'] }}, {{ $attBreakdown['absent'] }}, {{ $attBreakdown['other'] }}],
                    backgroundColor: ['#15803D', '#2563EB', '#DC2626', '#D97706'],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: { legend: { display: false } },
            },
        });
    }
    @endif

    @if($hasFinanceChart)
    var fin = document.getElementById('financeChart');
    if (fin) {
        new Chart(fin, {
            type: 'bar',
            data: {
                labels: @json($chartMonths),
                datasets: [
                    { label: 'Penerimaan', data: @json($revenueData), backgroundColor: '#0F766E', borderRadius: 6 },
                    { label: 'Pengeluaran', data: @json($expenseData), backgroundColor: '#F59E0B', borderRadius: 6 },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: function (v) { return (v / 1000000).toFixed(1) + 'jt'; } } },
                    x: { grid: { display: false } },
                },
            },
        });
    }
    @endif
})();
</script>
@endif
@endpush

@endsection
