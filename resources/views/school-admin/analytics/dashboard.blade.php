@extends('layouts.school-admin')
@section('title', 'School Intelligence')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

@php
    $riskTone = fn ($lvl) => match ($lvl) { 'critical' => 'danger', 'high' => 'danger', 'medium' => 'warning', default => 'success' };
    $riskLabel = fn ($lvl) => ucfirst($lvl);
    $riskCount = $atRisk->count() + $dropouts->count();
@endphp

<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div>
            <div class="text-sm text-[var(--color-text-muted)]">AI & Analitik</div>
            <h1 class="page-title mt-1">School Intelligence</h1>
            <p class="text-sm text-[var(--color-text-secondary)] mt-1">
                Ringkasan risiko siswa, prediksi dropout, dan sinyal peringatan dini — berbasis data nyata.
            </p>
        </div>
        <div class="flex gap-2">
            <x-ui.button href="{{ route('admin.analytics.risks.index') }}" variant="secondary">Semua Risk Score</x-ui.button>
            <x-ui.button href="{{ route('admin.analytics.dropout-risk.index') }}" variant="secondary">Prediksi Dropout</x-ui.button>
        </div>
    </div>

    {{-- KPI --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @php
            $kpis = [
                ['label' => 'Total Siswa',          'value' => number_format($totalStudents), 'tone' => null],
                ['label' => 'Siswa Dinilai',       'value' => number_format($assessed),      'tone' => null],
                ['label' => 'At-Risk (High/Crit)', 'value' => number_format($atRisk->count()), 'tone' => 'danger'],
                ['label' => 'Prediksi Dropout',    'value' => number_format($dropouts->count()), 'tone' => 'warning'],
            ];
        @endphp
        @foreach($kpis as $k)
            <div class="card card-pad">
                <div class="text-sm text-[var(--color-text-secondary)]">{{ $k['label'] }}</div>
                <div class="mt-1 text-2xl font-extrabold" style="{{ $k['tone'] ? 'color: var(--color-'.($k['tone'] === 'danger' ? 'danger' : 'warning').');' : '' }}">{{ $k['value'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Distribution --}}
    <div class="card card-pad">
        <div class="flex items-center justify-between mb-4">
            <h2 class="section-title">Distribusi Risiko</h2>
            @if($lastSnapshot)<span class="text-xs text-[var(--color-text-muted)]">Snapshot terakhir: {{ $lastSnapshot->format('d M Y') }}</span>@endif
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach(['low', 'medium', 'high', 'critical'] as $lvl)
                <div class="flex items-center justify-between p-4 rounded-lg" style="background: var(--color-surface-hover);">
                    <span class="text-sm font-medium">{{ $riskLabel($lvl) }}</span>
                    <x-ui.badge :variant="$riskTone($lvl)">{{ $distribution[$lvl] ?? 0 }}</x-ui.badge>
                </div>
            @endforeach
        </div>
    </div>

    {{-- At-Risk students --}}
    <div class="card">
        <div class="flex items-center justify-between px-5 py-4 border-b border-[var(--color-border)]">
            <h2 class="section-title">Siswa At-Risk</h2>
            <span class="badge badge-danger">{{ $atRisk->count() }}</span>
        </div>
        @if($atRisk->isEmpty())
            <div class="p-6"><x-feedback.empty-state icon="check" title="Tidak ada siswa at-risk" description="Tidak ada siswa dengan risiko tinggi atau kritis pada snapshot terakhir." /></div>
        @else
            <div class="table-scroll">
                <table class="table-elite">
                    <thead>
                        <tr><th>Siswa</th><th>Attendance</th><th>Academic</th><th>Behavior</th><th>Risk Score</th><th>Faktor</th></tr>
                    </thead>
                    <tbody>
                        @foreach($atRisk as $r)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.students.show', $r->student_id) }}" class="font-semibold text-[var(--color-primary)] hover:underline">{{ $r->student?->user?->name ?? 'Siswa #'.$r->student_id }}</a>
                                </td>
                                <td>{{ $r->attendance_score }}</td>
                                <td>{{ $r->academic_score }}</td>
                                <td>{{ $r->behavior_score }}</td>
                                <td><x-ui.badge :variant="$riskTone($r->risk_level)">{{ $r->overall_risk }} · {{ $r->risk_level }}</x-ui.badge></td>
                                <td class="text-xs text-[var(--color-text-secondary)]">{{ is_array($r->top_risk_factors) ? implode(', ', $r->top_risk_factors) : $r->top_risk_factors }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Dropout predictions --}}
    <div class="card">
        <div class="flex items-center justify-between px-5 py-4 border-b border-[var(--color-border)]">
            <h2 class="section-title">Prediksi Dropout (AI)</h2>
            <span class="badge badge-warning">{{ $dropouts->count() }}</span>
        </div>
        @if($dropouts->isEmpty())
            <div class="p-6"><x-feedback.empty-state icon="check" title="Tidak ada prediksi dropout" description="Belum ada siswa dengan risiko dropout signifikan." /></div>
        @else
            <div class="table-scroll">
                <table class="table-elite">
                    <thead>
                        <tr><th>Siswa</th><th>Risk Level</th><th>Skor</th><th>Tindakan yang disarankan</th></tr>
                    </thead>
                    <tbody>
                        @foreach($dropouts as $d)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.students.show', $d->student_id) }}" class="font-semibold text-[var(--color-primary)] hover:underline">{{ $d->student?->user?->name ?? 'Siswa #'.$d->student_id }}</a>
                                </td>
                                <td><x-ui.badge :variant="$riskTone($d->risk_level)">{{ $d->risk_level }}</x-ui.badge></td>
                                <td>{{ $d->risk_score }}</td>
                                <td class="text-xs text-[var(--color-text-secondary)]">{{ is_array($d->recommended_actions) ? implode('; ', $d->recommended_actions) : $d->recommended_actions }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
