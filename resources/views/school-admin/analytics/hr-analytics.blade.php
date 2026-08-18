@extends('layouts.school-admin')
@section('title', 'HR Analytics')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="space-y-6">
    <div>
        <div class="text-sm text-[var(--color-text-muted)]">Analytics</div>
        <h1 class="page-title mt-1">HR Analytics</h1>
        <p class="text-sm text-[var(--color-text-secondary)] mt-1">Analisis SDM — rasio guru-siswa, izin, kontrak, KPI, dan pelatihan.</p>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="card card-pad">
            <div class="text-xs text-[var(--color-text-muted)]">Total Staff</div>
            <div class="text-2xl font-extrabold mt-1">{{ number_format($totalStaff) }}</div>
        </div>
        <div class="card card-pad">
            <div class="text-xs text-[var(--color-text-muted)]">Rasio Guru : Siswa</div>
            <div class="text-2xl font-extrabold mt-1">1 : {{ $ratio }}</div>
        </div>
        <div class="card card-pad">
            <div class="text-xs text-[var(--color-text-muted)]">Tingkat Cuti Disetujui</div>
            <div class="text-2xl font-extrabold mt-1" style="color: var(--color-success);">{{ $leaveRate }}%</div>
        </div>
        <div class="card card-pad">
            <div class="text-xs text-[var(--color-text-muted)]">Tingkat Pelatihan</div>
            <div class="text-2xl font-extrabold mt-1" style="color: var(--color-info);">{{ $trainingRate }}%</div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-4">
        {{-- Avg Salary by Dept --}}
        <div class="card">
            <div class="px-5 py-4 border-b border-[var(--color-border)]">
                <h2 class="section-title">Rata-rata Gaji per Departemen</h2>
            </div>
            <div class="table-scroll">
                <table class="table-elite">
                    <thead><tr><th>Departemen</th><th>Rata-rata Gaji</th><th>Jumlah</th></tr></thead>
                    <tbody>
                        @foreach($avgSalaryByDept as $d)
                        <tr>
                            <td class="font-semibold capitalize">{{ $d->department ?: '-' }}</td>
                            <td>Rp {{ number_format($d->avg_salary / 100, 0, ',', '.') }}</td>
                            <td>{{ $d->count }}</td>
                        </tr>
                        @endforeach
                        @if($avgSalaryByDept->isEmpty())
                            <tr><td colspan="3" class="text-center text-[var(--color-text-muted)] py-6">Belum ada data</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- KPI Distribution --}}
        <div class="card">
            <div class="px-5 py-4 border-b border-[var(--color-border)]">
                <h2 class="section-title">Distribusi Skor KPI</h2>
            </div>
            <div class="p-4">
                @if($kpiDistribution->isEmpty())
                    <div class="text-center text-[var(--color-text-muted)] py-6">Belum ada data KPI</div>
                @else
                    @php $colors = ['Excellent' => 'success', 'Good' => 'info', 'Fair' => 'warning', 'Needs Improvement' => 'danger']; @endphp
                    @foreach($kpiDistribution as $kpi)
                    <div class="flex items-center justify-between py-2 border-b border-[var(--color-border)] last:border-0">
                        <span class="text-sm">{{ $kpi->label }}</span>
                        <x-ui.badge :variant="$colors[$kpi->label] ?? 'info'">{{ $kpi->total }}</x-ui.badge>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- Contract Expiry --}}
    <div class="card">
        <div class="px-5 py-4 border-b border-[var(--color-border)]">
            <h2 class="section-title">Kontrak Habis dalam 6 Bulan</h2>
        </div>
        @if($contractExpiry->isEmpty())
            <div class="p-6"><x-feedback.empty-state icon="check" title="Tidak ada kontrak yang akan habis" /></div>
        @else
            <div class="table-scroll">
                <table class="table-elite">
                    <thead><tr><th>Staff</th><th>Tipe</th><th>Tanggal Habis</th><th>Sisa Hari</th></tr></thead>
                    <tbody>
                        @foreach($contractExpiry as $c)
                        <tr>
                            <td class="font-semibold">{{ $c->staff?->user?->name ?? '-' }}</td>
                            <td class="capitalize">{{ $c->type }}</td>
                            <td>{{ $c->end_date?->format('d M Y') }}</td>
                            <td>
                                @php $days = $c->end_date->diffInDays(now()); @endphp
                                <x-ui.badge :variant="$days <= 30 ? 'danger' : ($days <= 90 ? 'warning' : 'info')">{{ $days }} hari</x-ui.badge>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Leave by Type --}}
    @if($leaveByType->isNotEmpty())
    <div class="card card-pad">
        <h2 class="section-title mb-3">Cuti per Tipe</h2>
        <div class="grid sm:grid-cols-3 gap-3">
            @foreach($leaveByType as $l)
            <div class="p-4 rounded-lg" style="background: var(--color-surface-hover);">
                <div class="text-xs text-[var(--color-text-muted)] capitalize">{{ $l->type }}</div>
                <div class="text-lg font-bold mt-1">{{ $l->total_days }} hari</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
