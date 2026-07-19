@extends('layouts.school-admin')
@section('title', 'Discipline Leaderboard')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><h1 class="elite-h1 text-3xl ink-primary mb-2">Leaderboard Disiplin</h1><div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">Total poin disiplin per siswa (positif = prestasi, negatif = pelanggaran).</p></div>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">#</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Catatan</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Total Poin</th>
</tr></thead><tbody>
@forelse($rows as $i => $r)<tr class="border-t border-rule">
<td class="px-3 py-3 font-display text-lg ink-accent">{{ $i+1 }}</td>
<td class="px-3 py-3"><div class="font-serif font-semibold">{{ $r->student_name }}</div><div class="text-xs text-gray-500">{{ $r->admission_no }}</div></td>
<td class="px-3 py-3 text-right font-mono">{{ $r->record_count }}</td>
<td class="px-3 py-3 text-right font-display text-lg {{ $r->total_points >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ $r->total_points > 0 ? '+' : '' }}{{ $r->total_points }}</td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada catatan disiplin.</td></tr>@endforelse
</tbody></table></div>
@endsection
