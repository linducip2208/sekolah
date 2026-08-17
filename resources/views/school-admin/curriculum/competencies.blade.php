@extends('layouts.school-admin')
@section('title', 'Kompetensi (CP/TP/ATP)')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Competentia</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Kompetensi Kurikulum (CP / TP / ATP)</h1>
    <div class="elite-rule"></div>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Kompetensi</summary>
    <form method="POST" action="{{ route('admin.curriculum.competencies.store') }}" class="px-5 py-4 border-t border-rule grid md:grid-cols-3 gap-2">@csrf
        <select name="curriculum_framework_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— framework —</option>
            @foreach($frameworks as $f)<option value="{{ $f->id }}">{{ $f->name }} ({{ $f->type }})</option>@endforeach
        </select>
        <select name="subject_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— mapel —</option>
            @foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
        </select>
        <select name="class_room_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— kelas —</option>
            @foreach($classRooms as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
        </select>
        <input name="code" required maxlength="30" placeholder="Kode (mis. CP-1.1)" class="border-2 border-rule px-3 py-2 font-mono text-sm">
        <select name="level_type" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            @foreach($levels as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
        </select>
        <select name="parent_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— induk (opsional) —</option>
            @foreach($parents as $p)<option value="{{ $p->id }}">{{ $p->code }} · {{ Str::limit($p->description, 40) }}</option>@endforeach
        </select>
        <textarea name="description" rows="2" required placeholder="Deskripsi kompetensi" class="md:col-span-3 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        <textarea name="indicators" rows="3" placeholder="Indikator (satu per baris)" class="md:col-span-3 border-2 border-rule px-3 py-2 font-mono text-xs"></textarea>
        <div class="md:col-span-3"><button class="btn-elite">Simpan</button></div>
    </form>
</details>

<form method="GET" class="bg-white border border-rule p-4 mb-4 flex gap-2 items-center">
    <select name="level_type" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">— Semua level —</option>
        @foreach($levels as $key => $label)<option value="{{ $key }}" @selected(request('level_type') === $key)>{{ $label }}</option>@endforeach
    </select>
    <select name="subject_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">— Semua mapel —</option>
        @foreach($subjects as $s)<option value="{{ $s->id }}" @selected(request('subject_id') == $s->id)>{{ $s->name }}</option>@endforeach
    </select>
    <button class="btn-elite" style="padding:.5rem 1rem;font-size:.65rem;">Filter</button>
</form>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white"><tr>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kode</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Deskripsi</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Level</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Mapel / Kelas</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Induk</th>
            <th class="px-4 py-3"></th>
        </tr></thead>
        <tbody>
            @forelse($competencies as $c)
            <tr class="border-t border-rule hover:bg-gray-50">
                <td class="px-4 py-3 font-mono text-xs">{{ $c->code }}</td>
                <td class="px-4 py-3 font-serif text-xs">{{ Str::limit($c->description, 70) }}</td>
                <td class="px-4 py-3"><span class="elite-kicker text-[.55rem]">{{ strtoupper($c->level_type) }}</span></td>
                <td class="px-4 py-3 text-xs">{{ $c->subject?->name }} · {{ $c->classRoom?->name }}</td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ $c->parent?->code ?? '—' }}</td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                    <details class="inline-block"><summary class="text-xs underline cursor-pointer ink-secondary">Edit</summary>
                        <form method="POST" action="{{ route('admin.curriculum.competencies.update', $c) }}" class="mt-2 grid gap-1">@csrf @method('PUT')
                            <input name="code" value="{{ $c->code }}" required class="border-2 border-rule px-2 py-1 font-mono text-xs">
                            <textarea name="description" rows="2" required class="border-2 border-rule px-2 py-1 font-serif text-xs">{{ $c->description }}</textarea>
                            <select name="level_type" class="border-2 border-rule px-2 py-1 font-serif text-xs">
                                @foreach($levels as $key => $label)<option value="{{ $key }}" @selected($c->level_type === $key)>{{ $label }}</option>@endforeach
                            </select>
                            <button class="text-xs text-left ink-accent">Simpan</button>
                        </form></details>
                    <form method="POST" action="{{ route('admin.curriculum.competencies.destroy', $c) }}" class="inline ml-2" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada kompetensi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $competencies->links() }}</div>

@endsection
