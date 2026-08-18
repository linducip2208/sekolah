@extends('layouts.school-admin')
@section('title', 'Detail Rubrik — ' . $rubric->name)
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Rubric Detail</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">{{ $rubric->name }}</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">
        @if($rubric->subject) Mapel: {{ $rubric->subject->name }} · @endif
        Max skor: {{ $rubric->max_score }} · {{ $rubric->criteria->count() }} kriteria
    </p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<div class="mb-4"><a href="{{ route('admin.rubrics.index') }}" class="text-sm ink-accent underline">&larr; Kembali ke daftar rubrik</a></div>

{{-- EDIT RUBRIC --}}
<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">Edit Rubrik</summary>
    <form method="POST" action="{{ route('admin.rubrics.update', $rubric) }}" class="px-5 py-4 border-t border-rule grid md:grid-cols-3 gap-2">@csrf @method('PUT')
        <input type="text" name="name" value="{{ $rubric->name }}" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <select name="subject_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— mapel —</option>
            @foreach($subjects as $s)<option value="{{ $s->id }}" {{ ($rubric->subject_id??'')===$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
        </select>
        <input type="number" name="max_score" value="{{ $rubric->max_score }}" min="1" max="100" class="border-2 border-rule px-3 py-2 font-mono text-sm">
        <textarea name="description" rows="2" class="md:col-span-3 border-2 border-rule px-3 py-2 font-serif text-sm">{{ $rubric->description }}</textarea>
        <div class="md:col-span-3"><button class="btn-elite">Update</button></div>
    </form>
</details>

{{-- ADD CRITERION --}}
<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Kriteria</summary>
    <form method="POST" action="{{ route('admin.rubrics.criterion.store') }}" class="px-5 py-4 border-t border-rule grid md:grid-cols-3 gap-2">@csrf
        <input type="hidden" name="rubric_id" value="{{ $rubric->id }}">
        <input type="text" name="name" required placeholder="Nama kriteria" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <input type="number" name="weight" value="1" min="1" max="10" placeholder="Bobot" class="border-2 border-rule px-3 py-2 font-mono text-sm">
        <input type="number" name="sort_order" value="0" min="0" placeholder="Urutan" class="border-2 border-rule px-3 py-2 font-mono text-sm">
        <textarea name="description" rows="2" placeholder="Deskripsi kriteria" class="md:col-span-3 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        <div class="md:col-span-3"><button class="btn-elite">Simpan Kriteria</button></div>
    </form>
</details>

<div class="space-y-4">
    @forelse($rubric->criteria->sortBy('sort_order') as $c)
    <div class="bg-white border border-rule p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="font-serif font-semibold">{{ $c->name }} <span class="text-xs text-gray-400 font-normal">bobot: {{ $c->weight }}</span></div>
                @if($c->description)<div class="text-sm text-gray-600 mt-1">{{ $c->description }}</div>@endif
            </div>
            <div class="flex gap-2 text-xs shrink-0">
                <form method="POST" action="{{ route('admin.rubrics.criterion.destroy', $c) }}" onsubmit="return confirm('Hapus kriteria ini?')">@csrf @method('DELETE')<button class="text-red-700 hover:underline">Hapus</button></form>
            </div>
        </div>

        {{-- LEVELS --}}
        <div class="mt-3 grid md:grid-cols-3 gap-2">
            @foreach($c->levels->sortByDesc('score') as $lv)
            <div class="bg-gray-50 border border-gray-200 rounded p-3">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-sm">{{ $lv->level_name }}</span>
                    <span class="font-mono text-xs bg-indigo-100 text-indigo-700 px-1.5 rounded">{{ $lv->score }}</span>
                </div>
                @if($lv->description)<div class="text-xs text-gray-600 mt-1">{{ $lv->description }}</div>@endif
            </div>
            @endforeach
        </div>

        {{-- Add Level --}}
        <details class="mt-2"><summary class="text-xs ink-accent cursor-pointer">+ Tambah Level</summary>
            <form method="POST" action="{{ route('admin.rubrics.level.store') }}" class="mt-1 grid grid-cols-4 gap-1">@csrf
                <input type="hidden" name="criteria_id" value="{{ $c->id }}">
                <input type="text" name="level_name" required placeholder="Nama level" class="border-2 border-rule px-2 py-1 font-serif text-xs">
                <input type="number" name="score" required min="0" max="100" placeholder="Skor" class="border-2 border-rule px-2 py-1 font-mono text-xs">
                <input type="text" name="description" placeholder="Deskripsi" class="col-span-2 border-2 border-rule px-2 py-1 font-serif text-xs">
                <div class="col-span-4"><button class="text-xs ink-accent">Simpan</button></div>
            </form>
        </details>
    </div>
    @empty
    <div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada kriteria rubrik.</div>
    @endforelse
</div>

@endsection
