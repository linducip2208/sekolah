@extends('layouts.school-admin')
@section('title', 'Tugas & PR Online')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<a href="{{ route('admin.classroom.lessons.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Materi</a>
<div class="mb-7"><div class="elite-kicker mb-2">Operationes</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Tugas & PR Online</h1><div class="elite-rule"></div></div>

<div class="flex gap-2 mb-5">
    <a href="{{ route('admin.assignments.create') }}" class="btn-elite text-xs">+ Tugas Baru</a>
</div>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Judul</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Materi</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tipe</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Deadline</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Submissions</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Nilai Max</th>
<th></th></tr></thead><tbody>
@forelse($assignments as $a)<tr class="border-t border-rule">
<td class="px-3 py-3 font-serif font-semibold">{{ $a->title }}</td>
<td class="px-3 py-3 text-xs">{{ $a->lesson?->title }}</td>
<td class="px-3 py-3 text-xs">
    @if($a->question_type === 'multiple_choice')<span class="text-blue-700">Pilihan Ganda</span>@elseif($a->question_type === 'mixed')<span class="text-amber-700">Campuran</span>@else<span class="text-gray-500">Essay</span>@endif
    @if($a->auto_grade)<span class="text-green-700 ml-1">(Auto)</span>@endif
</td>
<td class="px-3 py-3 text-xs {{ $a->due_date && now()->gt($a->due_date) ? 'text-red-700 font-bold' : '' }}">{{ $a->due_date?->format('d M Y H:i') ?? '—' }}</td>
<td class="px-3 py-3 text-center font-mono text-xs">{{ $a->submissions_count }}</td>
<td class="px-3 py-3 text-right font-mono">{{ $a->total_marks }}</td>
<td class="px-3 py-3 text-right space-x-1">
    <a href="{{ route('admin.assignments.edit', $a) }}" class="text-xs underline ink-secondary hover:ink-accent">Edit</a>
    <a href="{{ route('admin.assignments.submissions', $a) }}" class="text-xs underline text-blue-700">Nilai</a>
    <form method="POST" action="{{ route('admin.assignments.destroy', $a) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
</td>
</tr>@empty<tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada tugas.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $assignments->links() }}</div>
@endsection
