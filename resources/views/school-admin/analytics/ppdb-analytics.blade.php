@extends('layouts.school-admin')
@section('title', 'PPDB Analytics')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div>
            <div class="text-sm text-[var(--color-text-muted)]">Analytics</div>
            <h1 class="page-title mt-1">PPDB Analytics</h1>
            <p class="text-sm text-[var(--color-text-secondary)] mt-1">Funnel pendaftaran, analisis jalur zonasi, dan perbandingan periode.</p>
        </div>
        <form method="GET" class="flex gap-2">
            <select name="period_id" onchange="this.form.submit()" class="input-elite text-sm">
                <option value="">Semua Periode</option>
                @foreach($periods as $p)
                    <option value="{{ $p->id }}" {{ $periodId == $p->id ? 'selected' : '' }}>{{ $p->name ?? $p->year }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Funnel --}}
    <div class="card card-pad">
        <h2 class="section-title mb-4">Funnel Pendaftaran</h2>
        <div class="flex flex-col gap-3">
            @php
                $steps = [
                    ['key' => 'registered', 'label' => 'Terdaftar', 'color' => '#94A3B8'],
                    ['key' => 'submitted', 'label' => 'Submit Berkas', 'color' => '#3B82F6'],
                    ['key' => 'verified', 'label' => 'Terverifikasi', 'color' => '#8B5CF6'],
                    ['key' => 'accepted', 'label' => 'Diterima', 'color' => '#10B981'],
                    ['key' => 'enrolled', 'label' => 'Terdaftar Aktif', 'color' => '#059669'],
                ];
                $maxVal = max(array_column($funnelData, $steps[0]['key'] ?? 0), 1);
            @endphp
            @foreach($steps as $i => $step)
                @php
                    $val = $funnelData[$step['key']];
                    $pct = $funnelData['registered'] > 0 ? round($val / $funnelData['registered'] * 100, 1) : 0;
                    $w = max(($val / max($funnelData['registered'], 1)) * 100, 5);
                @endphp
                <div class="flex items-center gap-4">
                    <div class="w-32 text-sm text-right font-medium">{{ $step['label'] }}</div>
                    <div class="flex-1 relative h-10 rounded-lg overflow-hidden" style="background: var(--color-surface-muted);">
                        <div class="h-full rounded-lg transition-all" style="width: {{ $w }}%; background: {{ $step['color'] }};"></div>
                        <span class="absolute inset-0 flex items-center justify-center text-sm font-bold text-white drop-shadow">{{ $val }} ({{ $pct }}%)</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-4">
        {{-- Jalur Breakdown --}}
        <div class="card">
            <div class="px-5 py-4 border-b border-[var(--color-border)]">
                <h2 class="section-title">Breakdown per Jalur</h2>
            </div>
            <div class="table-scroll">
                <table class="table-elite">
                    <thead><tr><th>Jalur</th><th>Total</th><th>Diterima</th><th>Tingkat Diterima</th></tr></thead>
                    <tbody>
                        @foreach($jalurBreakdown as $j)
                        <tr>
                            <td class="font-semibold">{{ $j['jalur'] }}</td>
                            <td>{{ number_format($j['total']) }}</td>
                            <td>{{ number_format($j['accepted']) }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-16 h-1.5 rounded bg-[var(--color-surface-muted)]">
                                        <div class="h-full rounded bg-[var(--color-success)]" style="width: {{ $j['rate'] }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium">{{ $j['rate'] }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @if($jalurBreakdown->isEmpty())
                            <tr><td colspan="4" class="text-center text-[var(--color-text-muted)] py-6">Belum ada data</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Distance Analysis --}}
        <div class="card">
            <div class="px-5 py-4 border-b border-[var(--color-border)]">
                <h2 class="section-title">Analisis Jarak (Zonasi)</h2>
            </div>
            <div class="table-scroll">
                <table class="table-elite">
                    <thead><tr><th>Jarak</th><th>Total</th><th>Diterima</th></tr></thead>
                    <tbody>
                        @foreach($distanceAnalysis as $d)
                        <tr>
                            <td class="font-semibold">{{ $d['range'] }}</td>
                            <td>{{ number_format($d['total']) }}</td>
                            <td>{{ number_format($d['accepted']) }}</td>
                        </tr>
                        @endforeach
                        @if($distanceAnalysis->isEmpty())
                            <tr><td colspan="3" class="text-center text-[var(--color-text-muted)] py-6">Belum ada data</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Period Comparison --}}
    @if($currentPeriod)
    <div class="card card-pad">
        <h2 class="section-title mb-3">Perbandingan Periode</h2>
        <div class="grid sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-lg" style="background: var(--color-surface-hover);">
                <div class="text-xs text-[var(--color-text-muted)]">Periode Aktif</div>
                <div class="text-lg font-bold mt-1">{{ $currentPeriod->name ?? $currentPeriod->year }}</div>
            </div>
            <div class="p-4 rounded-lg" style="background: var(--color-surface-hover);">
                <div class="text-xs text-[var(--color-text-muted)]">Enrolled Saat Ini</div>
                <div class="text-lg font-bold mt-1 text-[var(--color-success)]">{{ $funnelData['enrolled'] }}</div>
            </div>
            <div class="p-4 rounded-lg" style="background: var(--color-surface-hover);">
                <div class="text-xs text-[var(--color-text-muted)]">Periode Sebelumnya</div>
                <div class="text-lg font-bold mt-1">{{ $previousEnrolled }}</div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
