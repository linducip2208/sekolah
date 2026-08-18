@extends('layouts.school-admin')
@section('title', 'Observasi Siswa')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Student Competency Assessment</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Observasi Siswa</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">Pencatatan pengamatan kompetensi siswa: akademik, non-akademik, sosial, dan emosional.</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<form method="GET" class="mb-5 bg-white border border-rule p-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Siswa</label>
        <select name="student_id" class="border-2 border-rule px-3 py-1.5 font-serif text-sm">
            <option value="">Semua</option>
            @foreach($students as $st)<option value="{{ $st->id }}" {{ request('student_id')==$st->id?'selected':'' }}>{{ $st->user?->name }}</option>@endforeach
        </select>
    </div>
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Tipe</label>
        <select name="observation_type" class="border-2 border-rule px-3 py-1.5 font-serif text-sm">
            <option value="">Semua</option>
            @foreach($obsTypes as $t)<option value="{{ $t }}" {{ request('observation_type')==$t?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$t)) }}</option>@endforeach
        </select>
    </div>
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Dari</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="border-2 border-rule px-3 py-1.5 font-mono text-sm">
    </div>
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Sampai</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="border-2 border-rule px-3 py-1.5 font-mono text-sm">
    </div>
    <button class="btn-elite text-xs">Filter</button>
</form>

<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Observasi</summary>
    <form method="POST" action="{{ route('admin.student-observations.store') }}" class="px-5 py-4 border-t border-rule grid md:grid-cols-3 gap-2">@csrf
        <select name="student_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— siswa —</option>
            @foreach($students as $st)<option value="{{ $st->id }}">{{ $st->user?->name }}</option>@endforeach
        </select>
        <input type="hidden" name="observer_id" value="{{ auth()->id() }}">
        <select name="subject_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— mapel (opsional) —</option>
            @foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
        </select>
        <select name="rubric_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— rubrik (opsional) —</option>
            @foreach($rubrics as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach
        </select>

        <input type="date" name="date" required value="{{ now()->toDateString() }}" class="border-2 border-rule px-3 py-2 font-mono text-sm">
        <select name="observation_type" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— tipe —</option>
            @foreach($obsTypes as $t)<option value="{{ $t }}">{{ ucfirst(str_replace('_',' ',$t)) }}</option>@endforeach
        </select>

        <textarea name="overall_notes" rows="2" placeholder="Catatan umum" class="md:col-span-3 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>

        <div class="md:col-span-3"><button class="btn-elite">Simpan Observasi</button></div>
    </form>
</details>

<div class="space-y-3">
    @forelse($observations as $o)
    <div class="bg-white border border-rule p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="font-serif font-semibold">
                    {{ $o->student?->user?->name ?? '—' }}
                    <span class="text-[.6rem] px-1.5 py-0.5 rounded
                        @if($o->observation_type==='akademik') bg-blue-100 text-blue-700
                        @elseif($o->observation_type==='non_akademik') bg-purple-100 text-purple-700
                        @elseif($o->observation_type==='sosial') bg-green-100 text-green-700
                        @else bg-amber-100 text-amber-700 @endif ml-1">{{ ucfirst(str_replace('_',' ',$o->observation_type)) }}</span>
                </div>
                <div class="text-xs text-gray-500 mt-0.5">
                    {{ $o->date->format('d F Y') }} · {{ $o->observer?->name ?? '—' }}
                    @if($o->subject) · {{ $o->subject->name }}@endif
                    @if($o->rubric) · Rubrik: {{ $o->rubric->name }}@endif
                </div>
            </div>
            <div class="flex gap-2 text-xs shrink-0">
                <details class="inline-block"><summary class="underline cursor-pointer ink-secondary">Edit</summary>
                    <form method="POST" action="{{ route('admin.student-observations.update', $o) }}" class="mt-2 grid gap-1 w-64">@csrf @method('PUT')
                        <select name="observation_type" class="border-2 border-rule px-2 py-1 font-serif text-xs">@foreach($obsTypes as $t)<option value="{{ $t }}" {{ $o->observation_type===$t?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$t)) }}</option>@endforeach</select>
                        <textarea name="overall_notes" rows="2" class="border-2 border-rule px-2 py-1 font-serif text-xs">{{ $o->overall_notes }}</textarea>
                        <button class="text-xs text-left ink-accent">Simpan</button>
                    </form></details>
                <form method="POST" action="{{ route('admin.student-observations.destroy', $o) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-red-700 hover:underline">Hapus</button></form>
            </div>
        </div>
        @if($o->overall_notes)<div class="text-sm mt-2">{{ $o->overall_notes }}</div>@endif
        @if($o->scores->count())
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach($o->scores as $sc)
            <div class="bg-gray-50 border border-gray-200 rounded px-3 py-1.5 text-xs">
                @if($sc->rubricCriterion)<span class="font-semibold">{{ $sc->rubricCriterion->name }}:</span> @endif
                <span class="font-mono bg-indigo-100 text-indigo-700 px-1 rounded">{{ $sc->score }}</span>
                @if($sc->notes)<span class="text-gray-500 ml-1">— {{ $sc->notes }}</span>@endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @empty
    <div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada observasi siswa.</div>
    @endforelse
</div>
<div class="mt-4">{{ $observations->links() }}</div>

@endsection
