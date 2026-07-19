@extends('layouts.school-admin')
@section('title', 'Daily Report Siswa')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Relationes Quotidianae</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Rapor Harian Siswa</h1><div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">Auto-generated harian — kombinasi absensi + nilai + kantin + UKS + disiplin per siswa.</p></div>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Absensi</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Klinik</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Disiplin</th>
</tr></thead><tbody>
@forelse($reports as $r)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs font-mono">{{ \Carbon\Carbon::parse($r->report_date)->format('d M Y') }}</td>
<td class="px-3 py-3 font-serif">{{ $r->student_name }}</td>
<td class="px-3 py-3 text-xs">{{ $r->attendance ?? '—' }}</td>
<td class="px-3 py-3 text-xs">{{ $r->clinic_visit ? 'Ya' : '—' }}</td>
<td class="px-3 py-3 text-xs">{{ $r->discipline_events ?? '—' }}</td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada daily report. Akan auto-generated dari job harian.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $reports->links() }}</div>
@endsection
