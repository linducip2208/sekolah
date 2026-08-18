@extends('layouts.school-admin')
@section('title', 'Executive Dashboard')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div>
            <div class="text-sm text-[var(--color-text-muted)]">Analytics</div>
            <h1 class="page-title mt-1">Executive Dashboard</h1>
            <p class="text-sm text-[var(--color-text-secondary)] mt-1">Ringkasan utama sekolah — siswa, guru, keuangan, dan kehadiran.</p>
        </div>
        <div class="flex gap-2">
            <select onchange="window.location.href='?months='+this.value" class="input-elite text-sm">
                @foreach([6,12,24] as $m)
                    <option value="{{ $m }}" {{ $monthsBack == $m ? 'selected' : '' }}>{{ $m }} bulan terakhir</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="card card-pad">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--color-primary-soft);">
                    <svg class="w-5 h-5 text-[var(--color-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <div class="text-xs text-[var(--color-text-muted)]">Total Siswa</div>
                    <div class="text-xl font-extrabold">{{ number_format($totalStudents) }}</div>
                </div>
            </div>
        </div>
        <div class="card card-pad">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--color-success-soft);">
                    <svg class="w-5 h-5 text-[var(--color-success)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <div class="text-xs text-[var(--color-text-muted)]">Total Guru/Staff</div>
                    <div class="text-xl font-extrabold">{{ number_format($totalTeachers) }}</div>
                </div>
            </div>
        </div>
        <div class="card card-pad">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--color-info-soft);">
                    <svg class="w-5 h-5 text-[var(--color-info)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-xs text-[var(--color-text-muted)]">Total Pendapatan</div>
                    <div class="text-xl font-extrabold">Rp {{ number_format($totalRevenue / 100, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="card card-pad">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--color-warning-soft);">
                    <svg class="w-5 h-5 text-[var(--color-warning)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-xs text-[var(--color-text-muted)]">Tingkat Kehadiran</div>
                    <div class="text-xl font-extrabold">{{ $attendanceRate }}%</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid lg:grid-cols-2 gap-4">
        <div class="card card-pad">
            <h2 class="section-title mb-4">Pendaftaran Siswa</h2>
            <canvas id="enrollmentChart" height="200"></canvas>
        </div>
        <div class="card card-pad">
            <h2 class="section-title mb-4">Pendapatan</h2>
            <canvas id="revenueChart" height="200"></canvas>
        </div>
    </div>

    <div class="card card-pad">
        <h2 class="section-title mb-4">Tren Kehadiran</h2>
        <canvas id="attendanceChart" height="120"></canvas>
    </div>

    <div class="grid lg:grid-cols-2 gap-4">
        {{-- Top Students --}}
        <div class="card">
            <div class="px-5 py-4 border-b border-[var(--color-border)]">
                <h2 class="section-title">Siswa Berprestasi</h2>
            </div>
            @if($topStudents->isEmpty())
                <div class="p-6"><x-feedback.empty-state icon="star" title="Belum ada data" /></div>
            @else
                <div class="table-scroll">
                    <table class="table-elite">
                        <thead><tr><th>Siswa</th><th>GPA</th><th>Rank</th></tr></thead>
                        <tbody>
                            @foreach($topStudents as $s)
                            <tr>
                                <td class="font-semibold">{{ $s->student?->user?->name ?? '-' }}</td>
                                <td><x-ui.badge variant="success">{{ number_format($s->gpa, 2) }}</x-ui.badge></td>
                                <td>{{ $s->rank ? '#'.$s->rank : '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Alerts --}}
        <div class="card">
            <div class="px-5 py-4 border-b border-[var(--color-border)]">
                <h2 class="section-title">Alert & Peringatan</h2>
            </div>
            <div class="p-4 space-y-2">
                @forelse($recentAlerts as $alert)
                    <div class="flex items-center gap-3 p-3 rounded-lg" style="background: var(--color-surface-hover);">
                        @if($alert['type'] === 'danger')
                            <span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>
                        @elseif($alert['type'] === 'warning')
                            <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>
                        @endif
                        <span class="text-sm">{{ $alert['message'] }}</span>
                    </div>
                @empty
                    <div class="p-4 text-center text-sm text-[var(--color-text-muted)]">Tidak ada alert aktif</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colors = {
        primary: '#2563EB', success: '#15803D', warning: '#D97706',
        primaryBg: 'rgba(37,99,235,0.12)', successBg: 'rgba(21,128,61,0.12)'
    };
    const defaultOpts = { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } };

    new Chart(document.getElementById('enrollmentChart'), {
        type: 'bar',
        data: {
            labels: @json($enrollmentTrend['labels']),
            datasets: [{ data: @json($enrollmentTrend['values']), backgroundColor: colors.primaryBg, borderColor: colors.primary, borderWidth: 1.5 }]
        }, options: defaultOpts
    });

    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: @json($revenueTrend['labels']),
            datasets: [{ data: @json($revenueTrend['values']), borderColor: colors.success, backgroundColor: colors.successBg, fill: true, tension: 0.3 }]
        }, options: defaultOpts
    });

    new Chart(document.getElementById('attendanceChart'), {
        type: 'line',
        data: {
            labels: @json($attendanceTrend['labels']),
            datasets: [{ data: @json($attendanceTrend['values']), borderColor: colors.warning, backgroundColor: 'rgba(217,119,6,0.08)', fill: true, tension: 0.3 }]
        }, options: { ...defaultOpts, scales: { y: { beginAtZero: true, max: 100 } } }
    });
});
</script>
@endsection
