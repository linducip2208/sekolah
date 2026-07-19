@extends('layouts.parent')
@section('title', 'Disiplin - '.$student->user?->name)
@section('content')
<a href="{{ route('portal.child', $student) }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← {{ $student->user?->name }}</a>
@include('parent-portal._child_tabs')

<div class="mb-6 bg-white border-l-4 {{ $totalPoints >= 0 ? 'border-green-700' : 'border-red-700' }} p-5">
<div class="elite-kicker text-[.65rem] mb-1">Total Poin Disiplin</div>
<div class="font-display text-3xl {{ $totalPoints >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ $totalPoints > 0 ? '+' : '' }}{{ $totalPoints }}</div>
</div>

<h2 class="elite-h2 text-2xl ink-primary mb-4">Catatan Disiplin & Prestasi</h2>
<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Kategori</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Poin</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Deskripsi</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tindakan</th>
</tr></thead><tbody>
@forelse($records as $r)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs">{{ $r->incident_date->format('d M Y') }}</td>
<td class="px-3 py-3 text-xs"><span class="elite-kicker text-[.55rem]">{{ $r->category?->name ?? '—' }}</span></td>
<td class="px-3 py-3 text-right font-mono {{ $r->points < 0 ? 'text-red-700' : 'text-green-700' }}">{{ $r->points > 0 ? '+' : '' }}{{ $r->points }}</td>
<td class="px-3 py-3 text-xs">{{ Str::limit($r->description, 80) }}</td>
<td class="px-3 py-3 text-xs">{{ $r->sanction_applied ?? '—' }}</td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Tidak ada catatan disiplin. 🎉</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $records->links() }}</div>
@endsection
