@extends('layouts.school-admin')
@section('title', 'PROMES — Program Semester')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Semester Planning</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Program Semester (PROMES)</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">Rencana kegiatan per minggu beserta alokasi jam pelajaran.</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<form method="GET" class="mb-5 bg-white border border-rule p-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Semester</label>
        <select name="semester_id" class="border-2 border-rule px-3 py-1.5 font-serif text-sm">
            <option value="">Semua</option>
            @foreach($semesters as $sm)<option value="{{ $sm->id }}" {{ request('semester_id')==$sm->id?'selected':'' }}>{{ $sm->academicYear?->name }} — {{ $sm->name }}</option>@endforeach
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
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah PROMES</summary>
    <form method="POST" action="{{ route('admin.promes.store') }}" class="px-5 py-4 border-t border-rule grid md:grid-cols-3 gap-2">@csrf
        <select name="semester_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— semester —</option>
            @foreach($semesters as $sm)<option value="{{ $sm->id }}">{{ $sm->academicYear?->name }} — {{ $sm->name }}</option>@endforeach
        </select>
        <select name="staff_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— guru —</option>
            @foreach($staffs as $s)<option value="{{ $s->id }}">{{ $s->user?->name }}</option>@endforeach
        </select>
        <select name="subject_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— mapel —</option>
            @foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
        </select>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Minggu ke-</label>
            <input type="number" name="week_number" required min="1" max="20" value="1" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Alokasi Jam</label>
            <input type="number" name="allocation_hours" min="0" max="100" value="0" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
        </div>
        <div class="md:col-span-3">
            <textarea name="activity_description" rows="2" placeholder="Deskripsi kegiatan" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        </div>
        <div class="md:col-span-3"><button class="btn-elite">Simpan PROMES</button></div>
    </form>
</details>

<div class="space-y-3">
    @forelse($programs as $p)
    <div class="bg-white border border-rule p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="font-serif font-semibold">
                    Minggu {{ $p->week_number }} · {{ $p->subject?->name }} — {{ $p->staff?->user?->name ?? '—' }}
                </div>
                <div class="text-xs text-gray-500 mt-0.5">
                    {{ $p->semester?->academicYear?->name }} {{ $p->semester?->name }} · {{ $p->allocation_hours }} JP
                    @if($p->status === 'approved')<span class="ml-2 px-2 py-0.5 bg-green-100 text-green-700 rounded text-[.6rem] font-semibold">Approved</span>
                    @else<span class="ml-2 px-2 py-0.5 bg-gray-100 text-gray-500 rounded text-[.6rem] font-semibold">Draft</span>@endif
                </div>
            </div>
            <div class="flex gap-2 text-xs shrink-0">
                <details class="inline-block"><summary class="underline cursor-pointer ink-secondary">Edit</summary>
                    <form method="POST" action="{{ route('admin.promes.update', $p) }}" class="mt-2 grid gap-1 w-64">@csrf @method('PUT')
                        <input type="number" name="week_number" value="{{ $p->week_number }}" min="1" max="20" class="border-2 border-rule px-2 py-1 font-mono text-xs">
                        <input type="number" name="allocation_hours" value="{{ $p->allocation_hours }}" min="0" max="100" class="border-2 border-rule px-2 py-1 font-mono text-xs">
                        <textarea name="activity_description" rows="2" class="border-2 border-rule px-2 py-1 font-serif text-xs">{{ $p->activity_description }}</textarea>
                        <select name="status" class="border-2 border-rule px-2 py-1 font-serif text-xs"><option value="draft" {{ $p->status==='draft'?'selected':'' }}>Draft</option><option value="approved" {{ $p->status==='approved'?'selected':'' }}>Approved</option></select>
                        <button class="text-xs text-left ink-accent">Simpan</button>
                    </form></details>
                <form method="POST" action="{{ route('admin.promes.destroy', $p) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-red-700 hover:underline">Hapus</button></form>
            </div>
        </div>
        @if($p->activity_description)<div class="text-sm mt-2"><b>Kegiatan:</b> {{ $p->activity_description }}</div>@endif
    </div>
    @empty
    <div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada PROMES.</div>
    @endforelse
</div>
<div class="mt-4">{{ $programs->links() }}</div>

@endsection
