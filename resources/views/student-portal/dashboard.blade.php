@extends('layouts.parent')
@section('title', 'Beranda Siswa')
@section('content')
@include('student-portal._nav')

<div class="mb-7">
<div class="elite-kicker mb-2">{{ now()->translatedFormat('l, d F Y') }}</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Halo, {{ $student->user?->name }}</h1>
<div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">{{ $student->classSection?->classRoom?->name }} {{ $student->classSection?->section?->name }} · NIS {{ $student->admission_no }}</p>
</div>

@php
    $present = (int) ($attendance30['present'] ?? 0);
    $absent = (int) ($attendance30['absent'] ?? 0);
    $late = (int) ($attendance30['late'] ?? 0);
    $totalA = $present + $absent + $late;
    $pct = $totalA > 0 ? round($present/$totalA*100, 1) : 0;
@endphp

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-7">
<div class="bg-white border-l-4 border-green-600 p-5">
<div class="elite-kicker text-[.6rem]">% Kehadiran</div>
<div class="font-display text-2xl ink-primary mt-2">{{ $pct }}%</div>
<div class="text-xs text-gray-500 mt-1">{{ $present }}/{{ $totalA }} hari</div>
</div>
<div class="bg-white border-l-4 border-blue-600 p-5">
<div class="elite-kicker text-[.6rem]">Mapel Hari Ini</div>
<div class="font-display text-2xl ink-primary mt-2">{{ $todaySchedule->count() }}</div>
</div>
<div class="bg-white border-l-4 border-purple-600 p-5">
<div class="elite-kicker text-[.6rem]">Nilai Terbaru</div>
<div class="font-display text-2xl ink-primary mt-2">{{ $recentMarks->count() }}</div>
</div>
<div class="bg-white border-l-4 {{ $unpaidInvoices > 0 ? 'border-red-600' : 'border-gray-400' }} p-5">
<div class="elite-kicker text-[.6rem]">Tagihan Belum Bayar</div>
<div class="font-display text-2xl ink-primary mt-2">{{ $unpaidInvoices }}</div>
</div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
<div class="bg-white border border-rule p-7">
<h3 class="elite-h3 text-lg ink-primary mb-4">📅 Jadwal Hari Ini</h3>
@if($todaySchedule->isEmpty())
<p class="font-serif text-sm text-gray-500 italic">Tidak ada jadwal hari ini.</p>
@else
<div class="space-y-2">
@foreach($todaySchedule as $sl)
<div class="flex justify-between items-center p-3 border border-rule">
<div>
<div class="font-serif font-semibold ink-primary">{{ $sl->subject?->name }}</div>
<div class="text-xs text-gray-500">{{ $sl->teacher?->name }} {{ $sl->room ? '· '.$sl->room : '' }}</div>
</div>
<div class="font-mono text-sm ink-secondary">{{ \Carbon\Carbon::parse($sl->start_time)->format('H:i') }}–{{ \Carbon\Carbon::parse($sl->end_time)->format('H:i') }}</div>
</div>
@endforeach
</div>
@endif
</div>

<div class="bg-white border border-rule p-7">
<h3 class="elite-h3 text-lg ink-primary mb-4">✓ Nilai Terbaru</h3>
@if($recentMarks->isEmpty())
<p class="font-serif text-sm text-gray-500 italic">Belum ada nilai.</p>
@else
@foreach($recentMarks as $m)
<div class="flex justify-between items-center p-3 border-b border-rule last:border-0">
<div class="font-serif">{{ $m->subject?->name }}</div>
<div class="flex items-baseline gap-3">
<span class="font-mono text-sm">{{ $m->obtained_marks }}/{{ $m->total_marks }}</span>
<span class="font-display text-xl ink-primary">{{ $m->grade ?? '—' }}</span>
</div>
</div>
@endforeach
@endif
</div>
</div>
@endsection
