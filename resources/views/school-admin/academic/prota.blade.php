@extends('layouts.school-admin')
@section('title', 'PROTA — Program Tahunan')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Curriculum Planning</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Program Tahunan (PROTA)</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">Perencanaan kompetensi dan target penyelesaian per tahun ajaran.</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<form method="GET" class="mb-5 bg-white border border-rule p-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Tahun Ajaran</label>
        <select name="academic_year_id" class="border-2 border-rule px-3 py-1.5 font-serif text-sm">
            <option value="">Semua</option>
            @foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ request('academic_year_id')==$ay->id?'selected':'' }}>{{ $ay->name }}</option>@endforeach
        </select>
    </div>
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Guru</label>
        <select name="staff_id" class="border-2 border-rule px-3 py-1.5 font-serif text-sm">
            <option value="">Semua</option>
            @foreach($staffs as $s)<option value="{{ $s->id }}" {{ request('staff_id')==$s->id?'selected':'' }}>{{ $s->user?->name }}</option>@endforeach
        </select>
    </div>
    <button class="btn-elite text-xs">Filter</button>
</form>

<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah PROTA</summary>
    <form method="POST" action="{{ route('admin.prota.store') }}" class="px-5 py-4 border-t border-rule grid md:grid-cols-3 gap-2">@csrf
        <select name="academic_year_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— tahun ajaran —</option>
            @foreach($academicYears as $ay)<option value="{{ $ay->id }}">{{ $ay->name }}</option>@endforeach
        </select>
        <select name="staff_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— guru —</option>
            @foreach($staffs as $s)<option value="{{ $s->id }}">{{ $s->user?->name }}</option>@endforeach
        </select>
        <select name="subject_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— mapel —</option>
            @foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
        </select>
        <textarea name="competencies" rows="3" placeholder="Kompetensi (satu per baris)" class="md:col-span-3 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        <textarea name="target_completion" rows="3" placeholder="Target penyelesaian (satu per baris)" class="md:col-span-3 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        <div class="md:col-span-3"><button class="btn-elite">Simpan PROTA</button></div>
    </form>
</details>

<div class="space-y-3">
    @forelse($programs as $p)
    <div class="bg-white border border-rule p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="font-serif font-semibold">{{ $p->subject?->name }} — {{ $p->staff?->user?->name ?? '—' }}</div>
                <div class="text-xs text-gray-500 mt-0.5">{{ $p->academicYear?->name ?? '—' }}</div>
            </div>
            <div class="flex gap-2 text-xs shrink-0">
                <details class="inline-block"><summary class="underline cursor-pointer ink-secondary">Edit</summary>
                    <form method="POST" action="{{ route('admin.prota.update', $p) }}" class="mt-2 grid gap-1 w-64">@csrf @method('PUT')
                        <textarea name="competencies" rows="2" class="border-2 border-rule px-2 py-1 font-serif text-xs">{{ is_array($p->competencies) ? implode("\n", $p->competencies) : $p->competencies }}</textarea>
                        <textarea name="target_completion" rows="2" class="border-2 border-rule px-2 py-1 font-serif text-xs">{{ is_array($p->target_completion) ? implode("\n", $p->target_completion) : $p->target_completion }}</textarea>
                        <button class="text-xs text-left ink-accent">Simpan</button>
                    </form></details>
                <form method="POST" action="{{ route('admin.prota.destroy', $p) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-red-700 hover:underline">Hapus</button></form>
            </div>
        </div>
        @if($p->competencies && count($p->competencies))
        <div class="text-sm mt-2"><b>Kompetensi:</b>
            <ul class="list-disc list-inside text-gray-600 mt-1">@foreach($p->competencies as $c)<li>{{ $c }}</li>@endforeach</ul>
        </div>@endif
        @if($p->target_completion && count($p->target_completion))
        <div class="text-sm mt-1"><b>Target:</b>
            <ul class="list-disc list-inside text-gray-600 mt-1">@foreach($p->target_completion as $t)<li>{{ $t }}</li>@endforeach</ul>
        </div>@endif
    </div>
    @empty
    <div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada PROTA.</div>
    @endforelse
</div>
<div class="mt-4">{{ $programs->links() }}</div>

@endsection
