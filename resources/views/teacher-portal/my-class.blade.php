@extends('layouts.parent')
@section('title', 'Rombel Saya')
@section('content')

<a href="{{ route('teacher.dashboard') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Dashboard</a>

<div class="mb-7">
<div class="elite-kicker mb-2">Cura Classis</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">{{ $classSection->classRoom?->name }} {{ $classSection->section?->name }}</h1>
<div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">{{ $students->count() }} siswa di rombel ini.</p>
</div>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">NIS</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Nama</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem] text-green-300">Hadir</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem] text-yellow-300">Telat</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem] text-red-300">Absen</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem] text-blue-300">Izin</th>
</tr></thead><tbody>
@foreach($students as $s)
@php $att = $attendanceSummary[$s->id] ?? collect(); @endphp
<tr class="border-t border-rule">
<td class="px-3 py-3 font-mono text-xs">{{ $s->admission_no }}</td>
<td class="px-3 py-3 font-serif">{{ $s->user?->name }}</td>
<td class="text-center px-3 py-3 font-mono">{{ $att->where('status','present')->first()->cnt ?? 0 }}</td>
<td class="text-center px-3 py-3 font-mono">{{ $att->where('status','late')->first()->cnt ?? 0 }}</td>
<td class="text-center px-3 py-3 font-mono">{{ $att->where('status','absent')->first()->cnt ?? 0 }}</td>
<td class="text-center px-3 py-3 font-mono">{{ $att->where('status','on_leave')->first()->cnt ?? 0 }}</td>
</tr>
@endforeach
</tbody></table></div>
@endsection
