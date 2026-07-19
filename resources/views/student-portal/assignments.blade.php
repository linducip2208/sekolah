@extends('layouts.parent')
@section('title', 'Tugas')
@section('content')
@include('student-portal._nav')
<div class="mb-6"><h1 class="elite-h1 text-2xl ink-primary mb-2">Tugas</h1><div class="elite-rule"></div></div>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Mapel</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Judul Tugas</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Materi</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Deadline</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Nilai</th>
</tr></thead><tbody>
@forelse($assignments as $a)
@php $isOverdue = $a->due_date && \Carbon\Carbon::parse($a->due_date)->isPast(); @endphp
<tr class="border-t border-rule {{ $isOverdue ? 'bg-red-50' : '' }}">
<td class="px-3 py-3 text-xs"><span class="elite-kicker text-[.55rem]">{{ $a->lesson?->subject?->name ?? '—' }}</span></td>
<td class="px-3 py-3 font-serif font-semibold">{{ $a->title }}</td>
<td class="px-3 py-3 text-xs">{{ $a->lesson?->title }}</td>
<td class="px-3 py-3 text-xs {{ $isOverdue ? 'text-red-700 font-semibold' : '' }}">{{ \Carbon\Carbon::parse($a->due_date)->format('d M Y') }}</td>
<td class="px-3 py-3 text-right font-mono">{{ $a->total_marks }}</td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada tugas.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $assignments->links() }}</div>
@endsection
