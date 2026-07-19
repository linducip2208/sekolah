@extends('layouts.school-admin')
@section('title', 'Log Ibadah')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><h1 class="elite-h1 text-3xl ink-primary mb-2">Log Ibadah Harian Siswa</h1><div class="elite-rule"></div></div>
<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Subuh</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Dzuhur</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Ashar</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Maghrib</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Isya</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Tilawah</th>
</tr></thead><tbody>
@forelse($logs as $l)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs">{{ \Carbon\Carbon::parse($l->log_date)->format('d M') }}</td>
<td class="px-3 py-3 font-serif text-xs">{{ $l->student_name }}</td>
<td class="text-center text-xs">{{ $l->subuh ?? '—' }}</td>
<td class="text-center text-xs">{{ $l->dzuhur ?? '—' }}</td>
<td class="text-center text-xs">{{ $l->ashar ?? '—' }}</td>
<td class="text-center text-xs">{{ $l->maghrib ?? '—' }}</td>
<td class="text-center text-xs">{{ $l->isya ?? '—' }}</td>
<td class="text-center font-mono text-xs">{{ $l->tilawah_done ? $l->tilawah_ayah_count.' ayat' : '—' }}</td>
</tr>@empty<tr><td colspan="8" class="p-10 text-center text-gray-500 italic font-serif">Belum ada log ibadah.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
