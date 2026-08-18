@extends('layouts.school-admin')
@section('title', 'Rubrik Penilaian')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Assessment Rubrics</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Rubrik Penilaian</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">Template rubrik dengan kriteria dan level penilaian untuk asesmen siswa.</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<form method="GET" class="mb-5 bg-white border border-rule p-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Mapel</label>
        <select name="subject_id" class="border-2 border-rule px-3 py-1.5 font-serif text-sm">
            <option value="">Semua</option>
            @foreach($subjects as $s)<option value="{{ $s->id }}" {{ request('subject_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
        </select>
    </div>
    <button class="btn-elite text-xs">Filter</button>
</form>

<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Rubrik</summary>
    <form method="POST" action="{{ route('admin.rubrics.store') }}" class="px-5 py-4 border-t border-rule grid md:grid-cols-3 gap-2">@csrf
        <input type="text" name="name" required placeholder="Nama rubrik" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <select name="subject_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— mapel (opsional) —</option>
            @foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
        </select>
        <input type="number" name="max_score" value="4" min="1" max="100" placeholder="Skor max" class="border-2 border-rule px-3 py-2 font-mono text-sm">
        <textarea name="description" rows="2" placeholder="Deskripsi rubrik" class="md:col-span-3 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        <div class="md:col-span-3"><button class="btn-elite">Simpan Rubrik</button></div>
    </form>
</details>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($rubrics as $r)
    <a href="{{ route('admin.rubrics.show', $r) }}" class="bg-white border border-rule p-5 card-lift block">
        <div class="font-serif font-semibold text-lg">{{ $r->name }}</div>
        <div class="text-xs text-gray-500 mt-0.5">
            @if($r->subject) {{ $r->subject->name }} · @endif
            Max skor: {{ $r->max_score }} · {{ $r->criteria->count() }} kriteria
        </div>
        @if($r->description)<div class="text-sm text-gray-600 mt-2 line-clamp-2">{{ $r->description }}</div>@endif
        <div class="mt-3 space-y-1">
            @foreach($r->criteria->take(3) as $c)
            <div class="text-xs text-gray-500">· {{ $c->name }} <span class="text-gray-400">({{ $c->levels->count() }} level)</span></div>
            @endforeach
            @if($r->criteria->count() > 3)<div class="text-xs text-gray-400">+ {{ $r->criteria->count() - 3 }} lagi</div>@endif
        </div>
    </a>
    @empty
    <div class="md:col-span-3 bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada rubrik.</div>
    @endforelse
</div>
<div class="mt-4">{{ $rubrics->links() }}</div>

@endsection
