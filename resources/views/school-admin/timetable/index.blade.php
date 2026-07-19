@extends('layouts.school-admin')
@section('title', 'Jadwal Pelajaran')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Tabula Lectionum</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Jadwal Pelajaran</h1>
    <div class="elite-rule"></div>
</div>

<form method="GET" class="bg-white border border-rule p-5 mb-6 grid grid-cols-1 md:grid-cols-3 gap-3">
    <div class="md:col-span-2">
        <label class="elite-kicker text-[.6rem] block mb-1">Rombel</label>
        <select name="class_section_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— pilih rombel —</option>
            @foreach($classSections as $cs)
                <option value="{{ $cs->id }}" @selected($classSectionId == $cs->id)>{{ $cs->classRoom?->name }} {{ $cs->section?->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex items-end">
        <button class="btn-elite w-full" style="padding:.6rem 1rem;font-size:.65rem;">Tampilkan</button>
    </div>
</form>

@if($classSectionId)
    <details class="mb-6 bg-white border border-rule">
        <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Slot</summary>
        <form method="POST" action="{{ route('admin.timetable.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-3 gap-3">
            @csrf
            <input type="hidden" name="class_section_id" value="{{ $classSectionId }}">
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Hari</label>
                <select name="day_of_week" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    @foreach($days as $num => $name)<option value="{{ $num }}">{{ $name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Jam Mulai</label>
                <input type="time" name="start_time" required class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Jam Selesai</label>
                <input type="time" name="end_time" required class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Mapel</label>
                <select name="subject_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    @foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Guru</label>
                <select name="teacher_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    @foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Ruang</label>
                <input type="text" name="room" maxlength="50" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="R-101">
            </div>
            <div class="md:col-span-3"><button class="btn-elite">Tambah Slot</button></div>
        </form>
    </details>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-3">
        @foreach($days as $num => $name)
            <div class="bg-white border border-rule">
                <div class="bg-[var(--c-primary)] text-white px-3 py-2 elite-kicker text-[.65rem] text-center">{{ $name }}</div>
                <div class="p-2 space-y-2">
                    @forelse($slots[$num] ?? [] as $sl)
                        <div class="border border-rule p-2 text-xs">
                            <div class="font-mono ink-secondary">{{ \Carbon\Carbon::parse($sl->start_time)->format('H:i') }}–{{ \Carbon\Carbon::parse($sl->end_time)->format('H:i') }}</div>
                            <div class="font-serif font-semibold mt-1 ink-primary">{{ $sl->subject?->name }}</div>
                            <div class="text-gray-500">{{ $sl->teacher?->name }}</div>
                            @if($sl->room)<div class="text-gray-400">📍 {{ $sl->room }}</div>@endif
                            <form method="POST" action="{{ route('admin.timetable.destroy', $sl) }}" class="mt-1" onsubmit="return confirm('Hapus slot?')">
                                @csrf @method('DELETE')
                                <button class="text-[.6rem] text-red-700 hover:underline">× hapus</button>
                            </form>
                        </div>
                    @empty
                        <div class="text-center text-xs text-gray-400 italic py-3">—</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="bg-white border border-rule p-10 text-center text-gray-500 font-serif">
        Pilih rombel untuk melihat jadwal.
    </div>
@endif

@endsection
