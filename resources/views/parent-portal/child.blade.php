@extends('layouts.parent')
@section('title', $student->user?->name)
@section('content')

<a href="{{ route('portal.dashboard') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Anak Saya</a>

<div class="mb-7">
<div class="elite-kicker mb-2">NIS {{ $student->admission_no }}</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">{{ $student->user?->name }}</h1>
<div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">{{ $student->classSection?->classRoom?->name }} {{ $student->classSection?->section?->name }}</p>
</div>

@include('parent-portal._child_tabs')

@php
    $present = (int) ($attendance['present'] ?? 0);
    $absent  = (int) ($attendance['absent'] ?? 0);
    $late    = (int) ($attendance['late'] ?? 0);
    $leave   = (int) ($attendance['on_leave'] ?? 0);
    $totalA  = $present + $absent + $late + $leave;
    $pctHadir = $totalA > 0 ? round(($present + $late) / $totalA * 100, 1) : 0;
@endphp

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
<div class="bg-white border-l-4 border-green-600 p-5">
<div class="elite-kicker text-[.6rem]">% Kehadiran 30 Hari</div>
<div class="font-display text-2xl ink-primary mt-2">{{ $pctHadir }}%</div>
<div class="text-xs text-gray-500 mt-1">{{ $present }}/{{ $totalA }} hari</div>
</div>
<div class="bg-white border-l-4 border-yellow-600 p-5">
<div class="elite-kicker text-[.6rem]">Terlambat 30 Hari</div>
<div class="font-display text-2xl ink-primary mt-2">{{ $late }}</div>
</div>
<div class="bg-white border-l-4 border-red-600 p-5">
<div class="elite-kicker text-[.6rem]">Absen 30 Hari</div>
<div class="font-display text-2xl ink-primary mt-2">{{ $absent }}</div>
</div>
<div class="bg-white border-l-4 border-purple-600 p-5">
<div class="elite-kicker text-[.6rem]">Tunggakan SPP</div>
<div class="font-display text-xl ink-primary mt-2">Rp {{ number_format($outstanding/100, 0, ',', '.') }}</div>
</div>
</div>

<div class="bg-white border border-rule p-7">
<h3 class="elite-h3 text-lg ink-primary mb-4">10 Nilai Terbaru</h3>
@if($recentMarks->isEmpty())
<p class="font-serif text-sm text-gray-500 italic">Belum ada nilai tercatat.</p>
@else
<table class="w-full text-sm"><thead><tr class="border-b border-rule">
<th class="text-left py-2 elite-kicker text-[.6rem]">Mapel</th>
<th class="text-right py-2 elite-kicker text-[.6rem]">Nilai</th>
<th class="text-center py-2 elite-kicker text-[.6rem]">Grade</th>
</tr></thead><tbody>
@foreach($recentMarks as $m)<tr class="border-b border-rule last:border-0">
<td class="py-2 font-serif">{{ $m->subject?->name }}</td>
<td class="py-2 text-right font-mono">{{ $m->obtained_marks }}/{{ $m->total_marks }}</td>
<td class="py-2 text-center font-display text-lg ink-primary">{{ $m->grade ?? '—' }}</td>
</tr>@endforeach
</tbody></table>
@endif
</div>

@endsection
