@extends('layouts.school-admin')
@section('title', 'Capaian & Tujuan Pembelajaran')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Curriculum Competency</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">CP / TP / ATP</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">Capaian Pembelajaran (CP) dan Tujuan Pembelajaran (TP) berdasarkan jenjang pendidikan.</p>
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
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Jenjang</label>
        <select name="stage" class="border-2 border-rule px-3 py-1.5 font-serif text-sm">
            <option value="">Semua</option>
            @foreach($stages as $st)<option value="{{ $st }}" {{ request('stage')==$st?'selected':'' }}>{{ $st }}</option>@endforeach
        </select>
    </div>
    <button class="btn-elite text-xs">Filter</button>
</form>

{{-- CREATE CP --}}
<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah CP</summary>
    <form method="POST" action="{{ route('admin.learning-outcomes.store') }}" class="px-5 py-4 border-t border-rule grid md:grid-cols-3 gap-2">@csrf
        <select name="subject_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— mapel —</option>
            @foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
        </select>
        <select name="stage" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— jenjang —</option>
            @foreach($stages as $st)<option value="{{ $st }}">{{ $st }}</option>@endforeach
        </select>
        <input type="text" name="code" required placeholder="Kode CP" class="border-2 border-rule px-3 py-2 font-mono text-sm">
        <textarea name="description" rows="2" required placeholder="Deskripsi Capaian Pembelajaran" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        <input type="number" name="sort_order" value="0" min="0" placeholder="Urutan" class="border-2 border-rule px-3 py-2 font-mono text-sm">
        <div class="md:col-span-3"><button class="btn-elite">Simpan CP</button></div>
    </form>
</details>

<div class="space-y-3">
    @forelse($outcomes as $o)
    <div class="bg-white border border-rule p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="font-serif font-semibold">
                    <span class="font-mono text-xs bg-gray-100 px-1.5 py-0.5 rounded">{{ $o->code }}</span>
                    {{ $o->subject?->name }}
                    <span class="text-[.6rem] px-1.5 py-0.5 bg-indigo-50 text-indigo-700 rounded ml-1">{{ $o->stage }}</span>
                </div>
            </div>
            <div class="flex gap-2 text-xs shrink-0">
                <details class="inline-block"><summary class="underline cursor-pointer ink-secondary">Edit</summary>
                    <form method="POST" action="{{ route('admin.learning-outcomes.update', $o) }}" class="mt-2 grid gap-1 w-72">@csrf @method('PUT')
                        <input type="text" name="code" value="{{ $o->code }}" class="border-2 border-rule px-2 py-1 font-mono text-xs">
                        <textarea name="description" rows="2" class="border-2 border-rule px-2 py-1 font-serif text-xs">{{ $o->description }}</textarea>
                        <select name="stage" class="border-2 border-rule px-2 py-1 font-serif text-xs">@foreach($stages as $st)<option value="{{ $st }}" {{ $o->stage===$st?'selected':'' }}>{{ $st }}</option>@endforeach</select>
                        <button class="text-xs text-left ink-accent">Simpan</button>
                    </form></details>
                <form method="POST" action="{{ route('admin.learning-outcomes.destroy', $o) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-red-700 hover:underline">Hapus</button></form>
            </div>
        </div>
        <div class="text-sm mt-2">{{ $o->description }}</div>

        {{-- Child TP --}}
        <div class="mt-3 ml-4 border-l-2 border-indigo-100 pl-3 space-y-2">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tujuan Pembelajaran</div>
            @forelse($o->objectives as $tp)
            <div class="flex items-start justify-between gap-2">
                <div class="text-sm">
                    <span class="font-mono text-[.65rem] bg-indigo-50 text-indigo-600 px-1 rounded">{{ $tp->code }}</span>
                    {{ $tp->description }}
                </div>
                <div class="flex gap-1 text-xs shrink-0">
                    <details class="inline-block"><summary class="underline cursor-pointer ink-secondary">Edit</summary>
                        <form method="POST" action="{{ route('admin.learning-outcomes.objective.update', $tp) }}" class="mt-1 grid gap-1 w-64">@csrf @method('PUT')
                            <input type="text" name="code" value="{{ $tp->code }}" class="border-2 border-rule px-2 py-1 font-mono text-xs">
                            <textarea name="description" rows="2" class="border-2 border-rule px-2 py-1 font-serif text-xs">{{ $tp->description }}</textarea>
                            <button class="text-xs text-left ink-accent">Simpan</button>
                        </form></details>
                    <form method="POST" action="{{ route('admin.learning-outcomes.objective.destroy', $tp) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-red-700 hover:underline text-[.65rem]">×</button></form>
                </div>
            </div>
            @empty
            <div class="text-xs text-gray-400 italic">Belum ada TP.</div>
            @endforelse

            {{-- Add TP --}}
            <details class="mt-1"><summary class="text-xs ink-accent cursor-pointer">+ Tambah TP</summary>
                <form method="POST" action="{{ route('admin.learning-outcomes.objective.store') }}" class="mt-1 grid grid-cols-3 gap-1">@csrf
                    <input type="hidden" name="learning_outcome_id" value="{{ $o->id }}">
                    <input type="text" name="code" required placeholder="Kode" class="border-2 border-rule px-2 py-1 font-mono text-xs">
                    <input type="text" name="description" required placeholder="Deskripsi TP" class="col-span-2 border-2 border-rule px-2 py-1 font-serif text-xs">
                    <div class="col-span-3"><button class="text-xs ink-accent">Simpan</button></div>
                </form>
            </details>
        </div>
    </div>
    @empty
    <div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada Capaian Pembelajaran.</div>
    @endforelse
</div>
<div class="mt-4">{{ $outcomes->links() }}</div>

@endsection
