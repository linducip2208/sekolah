@extends('layouts.school-admin')
@section('title', 'Risk Analytics')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Periculum Discipulorum</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Risk Score Siswa</h1>
<div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">Skor risiko per siswa berdasarkan kehadiran, akademik, perilaku, dan engagement.</p></div>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Hadir</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Akademik</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Perilaku</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Engagement</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Overall</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Level</th>
</tr></thead><tbody>
@forelse($scores as $s)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs">{{ $s->snapshot_date?->format('d M Y') }}</td>
<td class="px-3 py-3 font-serif">{{ $s->student?->user?->name }}</td>
<td class="text-center px-3 py-3 font-mono text-xs">{{ $s->attendance_score }}</td>
<td class="text-center px-3 py-3 font-mono text-xs">{{ $s->academic_score }}</td>
<td class="text-center px-3 py-3 font-mono text-xs">{{ $s->behavior_score }}</td>
<td class="text-center px-3 py-3 font-mono text-xs">{{ $s->engagement_score }}</td>
<td class="text-center px-3 py-3 font-mono font-bold">{{ $s->overall_risk }}</td>
<td class="text-center px-3 py-3"><span class="text-xs px-2 py-0.5 rounded
{{ $s->risk_level === 'low' ? 'bg-green-100 text-green-700' : '' }}
{{ $s->risk_level === 'medium' ? 'bg-yellow-100 text-yellow-700' : '' }}
{{ $s->risk_level === 'high' ? 'bg-orange-100 text-orange-700' : '' }}
{{ $s->risk_level === 'critical' ? 'bg-red-100 text-red-700' : '' }}">{{ $s->risk_level }}</span></td>
</tr>@empty<tr><td colspan="8" class="p-10 text-center text-gray-500 italic font-serif">Belum ada snapshot risk score. Akan ter-generate otomatis dari job analytics harian.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $scores->links() }}</div>
@endsection
