@extends('layouts.school-admin')
@section('title', 'Materi / Lessons')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7 flex justify-between items-end"><div>
<div class="elite-kicker mb-2">Materiae</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Materi Pelajaran (Online Classroom)</h1><div class="elite-rule"></div></div>
<a href="{{ route('admin.classroom.assignments.index') }}" class="btn-elite-ghost">Tugas →</a></div>

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Materi</summary>
<form method="POST" action="{{ route('admin.classroom.lessons.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-2 gap-3">@csrf
<input name="title" required maxlength="255" placeholder="Judul materi" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
<select name="class_section_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— Rombel —</option>
@foreach($classSections as $cs)<option value="{{ $cs->id }}">{{ $cs->classRoom?->name }} {{ $cs->section?->name }}</option>@endforeach
</select>
<select name="subject_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— Mapel —</option>
@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</select>
<select name="teacher_id" required class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— Guru —</option>
@foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
</select>
<textarea name="description" rows="3" placeholder="Deskripsi/ringkasan" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
<div class="md:col-span-2"><button class="btn-elite">Simpan</button></div>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Judul</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Rombel</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Mapel</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Guru</th>
<th></th></tr></thead><tbody>
@forelse($lessons as $l)<tr class="border-t border-rule">
<td class="px-3 py-3 font-serif font-semibold">{{ $l->title }}</td>
<td class="px-3 py-3 text-xs">{{ $l->classSection?->classRoom?->name }} {{ $l->classSection?->section?->name }}</td>
<td class="px-3 py-3 text-xs">{{ $l->subject?->name }}</td>
<td class="px-3 py-3 text-xs">{{ $l->teacher?->name }}</td>
<td class="px-3 py-3 text-right"><form method="POST" action="{{ route('admin.classroom.lessons.destroy', $l) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form></td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada materi.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $lessons->links() }}</div>
@endsection
