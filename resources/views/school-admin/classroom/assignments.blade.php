@extends('layouts.school-admin')
@section('title', 'Tugas Online')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<a href="{{ route('admin.classroom.lessons.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Materi</a>
<div class="mb-7"><div class="elite-kicker mb-2">Operationes</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Tugas / Assignment</h1><div class="elite-rule"></div></div>

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Buat Tugas</summary>
<form method="POST" action="{{ route('admin.assignments.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-2 gap-3">@csrf
<input name="title" required maxlength="255" placeholder="Judul tugas" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
<select name="lesson_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— Materi —</option>
@foreach($lessons as $l)<option value="{{ $l->id }}">{{ $l->title }}</option>@endforeach
</select>
<input type="number" min="1" max="1000" name="total_marks" required value="100" placeholder="Total nilai" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<input type="datetime-local" name="due_date" required class="md:col-span-2 border-2 border-rule px-3 py-2 text-sm">
<textarea name="instructions" rows="3" placeholder="Petunjuk pengerjaan" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
<div class="md:col-span-2"><button class="btn-elite">Simpan</button></div>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Judul</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Materi</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Deadline</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Nilai</th>
<th></th></tr></thead><tbody>
@forelse($assignments as $a)<tr class="border-t border-rule">
<td class="px-3 py-3 font-serif font-semibold">{{ $a->title }}</td>
<td class="px-3 py-3 text-xs">{{ $a->lesson?->title }}</td>
<td class="px-3 py-3 text-xs">{{ $a->due_date }}</td>
<td class="px-3 py-3 text-right font-mono">{{ $a->total_marks }}</td>
<td class="px-3 py-3 text-right"><form method="POST" action="{{ route('admin.assignments.destroy', $a) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form></td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada tugas.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $assignments->links() }}</div>
@endsection
