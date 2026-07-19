@extends('layouts.school-admin')
@section('title', 'Attendance %')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><h1 class="elite-h1 text-3xl ink-primary mb-2">Persentase Kehadiran per Rombel</h1><div class="elite-rule"></div></div>

<form method="GET" class="bg-white border border-rule p-5 mb-6 flex gap-3 items-end">
<div><label class="elite-kicker text-[.6rem] block mb-1">Bulan</label>
<input type="month" name="month" value="{{ $month }}" class="border-2 border-rule px-3 py-2 font-mono text-sm"></div>
<button class="btn-elite" style="padding:.6rem 1rem;font-size:.65rem;">Tampilkan</button>
</form>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Rombel</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem] text-green-300">Hadir</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem] text-yellow-300">Telat</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem] text-red-300">Absen</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem] text-blue-300">Izin</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">% Kehadiran</th>
</tr></thead><tbody>
@forelse($rows as $r)<tr class="border-t border-rule">
<td class="px-4 py-3 font-serif font-semibold">{{ $r->class_name }} {{ $r->section_name }}</td>
<td class="text-center px-3 py-3 font-mono">{{ $r->present }}</td>
<td class="text-center px-3 py-3 font-mono">{{ $r->late }}</td>
<td class="text-center px-3 py-3 font-mono">{{ $r->absent }}</td>
<td class="text-center px-3 py-3 font-mono">{{ $r->leave }}</td>
<td class="text-right px-3 py-3 font-display text-xl ink-primary {{ $r->pct < 80 ? 'text-red-700' : ($r->pct < 90 ? 'text-yellow-700' : 'text-green-700') }}">{{ $r->pct }}%</td>
</tr>@empty<tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada rombel.</td></tr>@endforelse
</tbody></table></div>
@endsection
