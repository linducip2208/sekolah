@extends('layouts.school-admin')
@section('title', 'Dashboard')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

@php
    $ctx = $data['context'];
    $kpis = $data['kpis'];
    $alerts = $data['alerts'];
    $charts = $data['charts'];
    $lists = $data['lists'];

    $roleName = auth()->user()->getRoleNames()->first() ?? 'admin';
    $roleLabels = [
        'super_admin' => 'Super Admin', 'admin' => 'Administrator', 'principal' => 'Kepala Sekolah',
        'accountant' => 'Bendahara', 'hr' => 'HR', 'teacher' => 'Guru',
        'homeroom_teacher' => 'Wali Kelas', 'counselor' => 'Guru BK',
    ];
    $roleLabel = $roleLabels[$roleName] ?? ucfirst($roleName);

    // Aksi cepat dari NavigationService (role-aware, hanya route yang hidup)
    $quickActions = collect(app(\App\Services\Navigation\NavigationService::class)->quickCreateFor(auth()->user()))
        ->take(6)
        ->map(fn ($q) => [$q['url'], $q['label'], $q['icon']])
        ->values()
        ->all();
@endphp

<div class="space-y-6">

    {{-- ===== Header: greeting + context ===== --}}
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-3">
        <div class="min-w-0">
            <div class="text-sm text-[var(--color-text-muted)]">{{ $ctx['date'] }}</div>
            <h1 class="page-title mt-1">{{ $ctx['greeting'] }}, {{ Str::before(auth()->user()->name, ' ') }}.</h1>
            <p class="text-sm text-[var(--color-text-secondary)] mt-1 flex flex-wrap items-center gap-x-2 gap-y-1">
                <span>{{ $roleLabel }}</span>
                @if($ctx['school_name'])
                    <span aria-hidden="true">·</span><span class="truncate max-w-[16rem] sm:max-w-none">{{ $ctx['school_name'] }}</span>
                @endif
                @if($ctx['academic_year'] && $ctx['academic_year'] !== '—')
                    <span class="badge badge-primary">TA {{ $ctx['academic_year'] }}</span>
                @endif
                @if($ctx['semester'])
                    <span class="badge badge-info">{{ $ctx['semester'] }}</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <a href="{{ route('admin.my-work') }}" class="btn btn-secondary btn-sm relative">
                My Work
                @if($lists['my_work_total'] > 0)
                    <span class="inline-flex items-center justify-center min-w-5 h-5 px-1 rounded-full text-[11px] font-bold text-white" style="background: var(--color-danger);">{{ $lists['my_work_total'] }}</span>
                @endif
            </a>
        </div>
    </div>

    {{-- ===== Setup sekolah (hanya jika belum 100%) ===== --}}
    @if($data['setup']['percent'] < 100 && in_array($roleName, ['super_admin', 'admin'], true))
        <div class="card card-pad">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1.5">
                        <h2 class="section-title">Penyiapan Sekolah</h2>
                        <span class="badge {{ $data['setup']['percent'] >= 70 ? 'badge-success' : 'badge-warning' }}">{{ $data['setup']['percent'] }}%</span>
                    </div>
                    <p class="text-xs text-[var(--color-text-secondary)] mb-2">{{ $data['setup']['done'] }} dari {{ $data['setup']['total'] }} langkah dasar selesai.</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($data['setup']['steps'] as $step)
                            <a href="{{ $step['href'] }}"
                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border transition"
                               style="{{ $step['done']
                                   ? 'background: var(--color-success-soft); color: var(--color-success); border-color: transparent;'
                                   : 'background: var(--color-surface); color: var(--color-text-secondary); border-color: var(--color-border-strong);' }}">
                                @if($step['done'])
                                    <x-ui.icon name="check" class="w-3 h-3" />{{ $step['label'] }}
                                @else
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.4"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>{{ $step['label'] }}
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="w-full sm:w-40 flex-shrink-0" aria-hidden="true">
                    <div class="h-2 rounded-full overflow-hidden" style="background: var(--color-surface-muted);">
                        <div class="h-full rounded-full transition-all" style="width: {{ $data['setup']['percent'] }}%; background: linear-gradient(90deg, var(--color-primary), var(--color-accent));"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== KPI utama (maks 6, satu baris per role) ===== --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-{{ count($kpis) > 4 ? '6' : '4' }} gap-3 sm:gap-4">
        @foreach($kpis as $k)
            <a href="{{ $k['href'] }}" class="card card-pad card-hover block group">
                <div class="flex items-start justify-between gap-2">
                    <div class="text-[12px] text-[var(--color-text-secondary)] leading-snug min-h-[2rem]">{{ $k['label'] }}</div>
                    @isset($k['tone'])
                        <span class="h-7 w-7 rounded-lg flex items-center justify-center flex-shrink-0 transition-transform group-hover:scale-105"
                              style="background: var(--color-{{ $k['tone'] }}-soft); color: var(--color-{{ $k['tone'] }});" aria-hidden="true">
                            <span class="h-2 w-2 rounded-full" style="background: currentColor;"></span>
                        </span>
                    @endisset
                </div>
                <div class="mt-1.5 text-2xl font-extrabold tracking-tight tabular-nums" style="color: var(--color-text);">{{ $k['value'] ?? '—' }}</div>
            </a>
        @endforeach
    </div>

    {{-- ===== Perlu Perhatian + Aksi Cepat ===== --}}
    <div class="grid lg:grid-cols-12 gap-4">
        <div class="card lg:col-span-8">
            <div class="flex items-center justify-between px-5 py-4 border-b border-[var(--color-border)]">
                <h2 class="section-title">Perlu Perhatian</h2>
                @if(count($alerts))<span class="badge badge-danger">{{ count($alerts) }}</span>@endif
            </div>
            @if(empty($alerts))
                <div class="empty-state !py-8">
                    <div class="empty-icon"><x-ui.icon name="check" class="w-7 h-7" /></div>
                    <p class="empty-title">Semua beres.</p>
                    <p class="empty-desc">Tidak ada hal yang butuh perhatian saat ini.</p>
                </div>
            @else
                <div class="divide-y divide-[var(--color-border)]">
                    @foreach($alerts as $a)
                        <a href="{{ $a['href'] }}" class="flex items-center gap-3 px-5 py-3.5 hover:bg-[var(--color-surface-hover)] transition group">
                            <div class="h-9 w-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background: var(--color-{{ $a['tone'] }}-soft); color: var(--color-{{ $a['tone'] }});">
                                <x-ui.icon :name="$a['icon'] === 'admissions' ? 'users' : ($a['icon'] === 'money' ? 'money' : ($a['icon'] === 'clock' ? 'clock' : ($a['icon'] === 'users' ? 'user' : 'alert')))" class="w-5 h-5" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold">{{ $a['title'] }}</div>
                                <div class="text-xs text-[var(--color-text-muted)] mt-0.5">{{ $a['desc'] }}</div>
                            </div>
                            <span class="hidden sm:inline-flex text-[13px] font-semibold text-[var(--color-primary)] whitespace-nowrap">{{ $a['cta'] }}
                                <svg class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card card-pad lg:col-span-4">
            <h2 class="section-title mb-4">Aksi Cepat</h2>
            @if(empty($quickActions))
                <p class="text-sm text-[var(--color-text-muted)]">Buka ⌘K untuk lompat ke modul mana pun.</p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 gap-2">
                    @foreach($quickActions as $qa)
                        <a href="{{ $qa[0] }}" class="flex flex-col items-center gap-1.5 p-3 rounded-lg border border-[var(--color-border)] hover:border-[var(--color-primary)] hover:bg-[var(--color-primary-soft)] transition">
                            <x-ui.icon :name="$qa[2]" class="w-5 h-5 text-[var(--color-primary)]" />
                            <span class="text-xs font-medium text-center leading-tight">{{ $qa[1] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ===== Charts ===== --}}
    <div class="grid lg:grid-cols-12 gap-4">
        {{-- Kehadiran --}}
        <div class="card card-pad lg:col-span-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="section-title">Kehadiran Hari Ini</h2>
                @if($charts['attendance']['present_pct'] !== null)
                    <span class="badge {{ $charts['attendance']['present_pct'] >= 90 ? 'badge-success' : ($charts['attendance']['present_pct'] >= 80 ? 'badge-warning' : 'badge-danger') }}">{{ $charts['attendance']['present_pct'] }}%</span>
                @endif
            </div>
            @if(!$charts['attendance']['total'])
                <div class="empty-state !py-8">
                    <div class="empty-icon"><x-ui.icon name="calendar" class="w-6 h-6" /></div>
                    <p class="empty-desc">Belum ada data absensi hari ini.</p>
                </div>
            @else
                <div class="h-52"><canvas id="attendanceChart" role="img" aria-label="Ringkasan kehadiran hari ini"></canvas></div>
                <ul class="mt-3 space-y-1.5 text-sm">
                    @foreach([['present' => ['Hadir', 'success']], ['late' => ['Terlambat', 'info']], ['absent' => ['Alpha', 'danger']], ['other' => ['Izin / Setengah Hari', 'warning']]] as $st => [$label, $tone])
                        <li class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full" style="background: var(--color-{{ $tone }});"></span>
                            <span class="text-[var(--color-text-secondary)] flex-1">{{ $label }}</span>
                            <span class="font-semibold tabular-nums">{{ $charts['attendance']['breakdown'][$st] }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Keuangan --}}
        <div class="card card-pad lg:col-span-7">
            <div class="flex items-center justify-between mb-3">
                <h2 class="section-title">Penerimaan vs Pengeluaran</h2>
                <span class="text-xs text-[var(--color-text-muted)]">6 bulan terakhir</span>
            </div>
            @if(!$charts['finance']['enabled'])
                <div class="empty-state !py-8">
                    <div class="empty-icon"><x-ui.icon name="money" class="w-6 h-6" /></div>
                    <p class="empty-desc">Belum ada transaksi keuangan dalam 6 bulan terakhir.</p>
                </div>
            @else
                <div class="h-56"><canvas id="financeChart" role="img" aria-label="Tren penerimaan dan pengeluaran 6 bulan terakhir"></canvas></div>
            @endif
        </div>
    </div>

    {{-- ===== Siswa at-risk + My Work ===== --}}
    <div class="grid lg:grid-cols-12 gap-4">
        @if(!empty($lists['at_risk']))
        <div class="card lg:col-span-5">
            <div class="flex items-center justify-between px-5 py-4 border-b border-[var(--color-border)]">
                <h2 class="section-title">Siswa At-Risk</h2>
                <a href="{{ route('admin.analytics.risks.index') }}" class="text-sm text-[var(--color-primary)] hover:underline">Semua</a>
            </div>
            <ul class="divide-y divide-[var(--color-border)]">
                @foreach($lists['at_risk'] as $r)
                    <li class="flex items-center justify-between px-5 py-3">
                        <a href="{{ route('admin.students.show', $r['student_id']) }}" class="font-medium text-sm hover:underline truncate mr-2">{{ $r['name'] }}</a>
                        <x-ui.badge :variant="$r['level'] === 'critical' ? 'danger' : 'warning'">{{ $r['level'] }} · {{ $r['score'] }}</x-ui.badge>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="card {{ !empty($lists['at_risk']) ? 'lg:col-span-7' : 'lg:col-span-12' }}">
            <div class="flex items-center justify-between px-5 py-4 border-b border-[var(--color-border)]">
                <h2 class="section-title">Tugas Saya</h2>
                @if($lists['my_work_total'] > 0)<span class="badge badge-warning">{{ $lists['my_work_total'] }}</span>@endif
            </div>
            @if(empty($lists['my_work']))
                <div class="px-5 py-6 text-center text-sm text-[var(--color-text-muted)]">Semua tugas selesai. Tidak ada yang menunggu.</div>
            @else
                <ul class="divide-y divide-[var(--color-border)]">
                    @foreach($lists['my_work'] as $t)
                        <li>
                            <a href="{{ $t['href'] }}" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-[var(--color-surface-hover)] transition">
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium truncate">{{ $t['label'] }}</span>
                                    <span class="block text-xs text-[var(--color-text-muted)] truncate">{{ $t['desc'] }}</span>
                                </span>
                                <span class="flex items-center gap-2 flex-shrink-0">
                                    <span class="badge badge-{{ $t['priority'] === 'critical' ? 'danger' : ($t['priority'] === 'high' ? 'warning' : 'info') }}">{{ $t['count'] }}</span>
                                    <span class="text-[var(--color-text-muted)]">→</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                @if($lists['my_work_total'] > count($lists['my_work']))
                    <div class="px-5 py-3 border-t border-[var(--color-border)] text-center">
                        <a href="{{ route('admin.my-work') }}" class="text-sm font-semibold text-[var(--color-primary)] hover:underline">
                            Lihat semua {{ $lists['my_work_total'] }} pekerjaan →
                        </a>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- ===== Agenda + Aktivitas ===== --}}
    <div class="grid lg:grid-cols-12 gap-4">
        <div class="card lg:col-span-5">
            <div class="flex items-center justify-between px-5 py-4 border-b border-[var(--color-border)]">
                <h2 class="section-title">Agenda Mendatang</h2>
                <a href="{{ route('admin.calendar.index') }}" class="text-sm text-[var(--color-primary)] hover:underline">Kalender</a>
            </div>
            @if(empty($lists['events']))
                <div class="px-5 py-6 text-center text-sm text-[var(--color-text-muted)]">Belum ada agenda mendatang.</div>
            @else
                <ul class="divide-y divide-[var(--color-border)]">
                    @foreach($lists['events'] as $e)
                        <li class="flex items-center gap-3 px-5 py-3">
                            <div class="text-center leading-tight flex-shrink-0 w-11">
                                <div class="text-lg font-extrabold tabular-nums">{{ $e['day'] }}</div>
                                <div class="text-[10px] uppercase text-[var(--color-text-muted)]">{{ $e['month'] }}</div>
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-medium truncate">{{ $e['title'] }}</div>
                                <div class="text-xs text-[var(--color-text-muted)]">{{ $e['type'] }}</div>
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
            @if(empty($lists['activity']))
                <div class="px-5 py-6 text-center text-sm text-[var(--color-text-muted)]">Belum ada aktivitas tercatat.</div>
            @else
                <ol class="divide-y divide-[var(--color-border)]">
                    @foreach($lists['activity'] as $act)
                        <li class="flex items-center gap-3 px-5 py-3">
                            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background: var(--color-primary);"></span>
                            <div class="flex-1 min-w-0 text-sm">
                                <span class="font-medium">{{ $act['actor'] }}</span>
                                <span class="text-[var(--color-text-secondary)]">{{ $act['description'] }}</span>
                            </div>
                            <span class="text-xs text-[var(--color-text-muted)] whitespace-nowrap">{{ $act['ago'] }}</span>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </div>

</div>

@push('scripts')
@if($charts['attendance']['total'] || $charts['finance']['enabled'])
<script>
(function () {
    if (typeof Chart === 'undefined') return;

    // Theme-aware colors — selalu baca token design system saat render,
    // jadi chart ikut light/dark mode & white-label branding.
    function cssVar(name, fallback) {
        var v = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        return v || fallback;
    }
    Chart.defaults.font.family = "'Manrope', sans-serif";
    Chart.defaults.color = cssVar('--color-text-muted', '#94A3B8');

    var instances = [];

    @if($charts['attendance']['total'])
    var att = document.getElementById('attendanceChart');
    if (att) {
        function buildAtt() {
            return new Chart(att, {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Terlambat', 'Alpha', 'Izin / Setengah Hari'],
                    datasets: [{
                        data: [
                            {{ $charts['attendance']['breakdown']['present'] }},
                            {{ $charts['attendance']['breakdown']['late'] }},
                            {{ $charts['attendance']['breakdown']['absent'] }},
                            {{ $charts['attendance']['breakdown']['other'] }}
                        ],
                        backgroundColor: [cssVar('--color-success', '#15803D'), cssVar('--color-info', '#2563EB'), cssVar('--color-danger', '#DC2626'), cssVar('--color-warning', '#D97706')],
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
        instances.push(buildAtt);
    }
    @endif

    @if($charts['finance']['enabled'])
    var fin = document.getElementById('financeChart');
    if (fin) {
        function buildFin() {
            var gridColor = cssVar('--color-border', '#E2E8F0');
            return new Chart(fin, {
                type: 'bar',
                data: {
                    labels: @json($charts['finance']['months']),
                    datasets: [
                        { label: 'Penerimaan', data: @json($charts['finance']['revenue']), backgroundColor: cssVar('--color-primary', '#2563EB'), borderRadius: 6 },
                        { label: 'Pengeluaran', data: @json($charts['finance']['expense']), backgroundColor: cssVar('--color-accent', '#F59E0B'), borderRadius: 6 },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, boxHeight: 10 } } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor },
                            ticks: { callback: function (v) { return (v / 1000000).toFixed(1) + 'jt'; } },
                        },
                        x: { grid: { display: false } },
                    },
                },
            });
        }
        instances.push(buildFin);
    }
    @endif

    instances.forEach(function (build) { build(); });

    // Re-render chart saat tema berubah agar warna selalu terbaca.
    window.addEventListener('sikadpro:theme-changed', function () {
        instances.forEach(function (build, i) {
            if (Chart.getChart(document.querySelectorAll('canvas')[i])) {
                Chart.getChart(document.querySelectorAll('canvas')[i]).destroy();
            }
            build();
        });
    });
})();
</script>
@endif
@endpush

@endsection
