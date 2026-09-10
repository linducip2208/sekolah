@extends('layouts.parent')
@section('title', 'Beranda Siswa')
@section('content')
@include('student-portal._nav')

<div class="mb-6">
    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-1" style="color: var(--color-text);">Halo, {{ Str::before($student->user?->name, ' ') }}</h1>
    <p class="text-sm" style="color: var(--color-text-secondary);">{{ $student->classSection?->classRoom?->name }} {{ $student->classSection?->section?->name }} · NIS {{ $student->admission_no }}</p>
</div>

@php
    $present = (int) ($attendance30['present'] ?? 0);
    $absent = (int) ($attendance30['absent'] ?? 0);
    $late = (int) ($attendance30['late'] ?? 0);
    $totalA = $present + $absent + $late;
    $pct = $totalA > 0 ? round($present/$totalA*100, 1) : 0;
@endphp

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-7">
    <div class="card card-pad">
        <div class="text-[12px] font-medium" style="color: var(--color-text-secondary);">% Kehadiran</div>
        <div class="text-2xl font-extrabold tabular-nums mt-1" style="color: var(--color-success);">{{ $pct }}%</div>
        <div class="text-[11px] mt-0.5" style="color: var(--color-text-muted);">{{ $present }}/{{ $totalA }} hari</div>
    </div>
    <div class="card card-pad">
        <div class="text-[12px] font-medium" style="color: var(--color-text-secondary);">Pelajaran Hari Ini</div>
        <div class="text-2xl font-extrabold tabular-nums mt-1" style="color: var(--color-primary);">{{ $todaySchedule->count() }}</div>
    </div>
    <div class="card card-pad">
        <div class="text-[12px] font-medium" style="color: var(--color-text-secondary);">Nilai Terbaru</div>
        <div class="text-2xl font-extrabold tabular-nums mt-1" style="color: var(--color-info);">{{ $recentMarks->count() }}</div>
    </div>
    <div class="card card-pad">
        <div class="text-[12px] font-medium" style="color: var(--color-text-secondary);">Tagihan Belum Bayar</div>
        <div class="text-2xl font-extrabold tabular-nums mt-1" style="color: {{ $unpaidInvoices > 0 ? 'var(--color-danger)' : 'var(--color-success)' }};">{{ $unpaidInvoices }}</div>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-4">
    <div class="card">
        <div class="flex items-center gap-2 px-5 py-4 border-b" style="border-color: var(--color-border);">
            <x-ui.icon name="calendar" class="w-5 h-5 text-[var(--color-primary)]" />
            <h3 class="section-title">Jadwal Hari Ini</h3>
        </div>
        @if($todaySchedule->isEmpty())
            <x-feedback.empty-state icon="calendar" title="Tidak ada jadwal" description="Nikmati hari bebas Anda — tidak ada pelajaran terjadwal hari ini." />
        @else
            <ul class="divide-y" style="border-color: var(--color-border);">
                @foreach($todaySchedule as $sl)
                    <li class="flex justify-between items-center px-5 py-3.5 hover:bg-[var(--color-surface-hover)] transition">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold truncate" style="color: var(--color-text);">{{ $sl->subject?->name }}</div>
                            <div class="text-xs mt-0.5" style="color: var(--color-text-muted);">{{ $sl->teacher?->name }} {{ $sl->room ? '· '.$sl->room : '' }}</div>
                        </div>
                        <span class="text-sm font-semibold tabular-nums flex-shrink-0 ms-3" style="color: var(--color-text-secondary);">{{ \Carbon\Carbon::parse($sl->start_time)->format('H:i') }}–{{ \Carbon\Carbon::parse($sl->end_time)->format('H:i') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="card">
        <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color: var(--color-border);">
            <div class="flex items-center gap-2">
                <x-ui.icon name="chart" class="w-5 h-5 text-[var(--color-info)]" />
                <h3 class="section-title">Nilai Terbaru</h3>
            </div>
            <a href="{{ route('student.marks') }}" class="text-xs font-semibold" style="color: var(--color-primary);">Semua nilai →</a>
        </div>
        @if($recentMarks->isEmpty())
            <x-feedback.empty-state icon="chart" title="Belum ada nilai" description="Nilai akan muncul di sini setelah guru menilai pekerjaan Anda." />
        @else
            <ul class="divide-y" style="border-color: var(--color-border);">
                @foreach($recentMarks as $m)
                    <li class="flex justify-between items-center px-5 py-3.5">
                        <span class="text-sm font-medium truncate" style="color: var(--color-text);">{{ $m->subject?->name }}</span>
                        <span class="flex items-baseline gap-3 flex-shrink-0 ms-3">
                            <span class="text-xs tabular-nums" style="color: var(--color-text-muted);">{{ $m->obtained_marks }}/{{ $m->total_marks }}</span>
                            <span class="text-lg font-extrabold w-8 text-center" style="color: var(--color-primary);">{{ $m->grade ?? '—' }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
