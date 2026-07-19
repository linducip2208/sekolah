@extends('layouts.parent')
@section('title', 'Nilai - '.$student->user?->name)
@section('content')
<a href="{{ route('portal.child', $student) }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← {{ $student->user?->name }}</a>
@include('parent-portal._child_tabs')

<h2 class="elite-h2 text-2xl ink-primary mb-4">Catatan Nilai</h2>
<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Mapel</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Ujian</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Nilai</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Grade</th>
</tr></thead><tbody>
@forelse($marks as $m)<tr class="border-t border-rule">
<td class="px-3 py-3 font-serif font-semibold">{{ $m->subject?->name }}</td>
<td class="px-3 py-3 text-xs">{{ $m->exam?->title ?? '—' }}</td>
<td class="px-3 py-3 text-right font-mono">{{ $m->obtained_marks }}/{{ $m->total_marks }}</td>
<td class="px-3 py-3 text-center font-display text-xl ink-primary">{{ $m->grade ?? '—' }}</td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada nilai.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $marks->links() }}</div>
@endsection
