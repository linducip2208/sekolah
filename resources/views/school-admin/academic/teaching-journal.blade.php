@extends('layouts.school-admin')
@section('title', 'Jurnal Mengajar')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Diarium Docendi</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Jurnal Mengajar</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">Catatan harian guru: materi, aktivitas, partisipasi siswa, dan refleksi.</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

{{-- FILTERS --}}
<form method="GET" class="mb-5 bg-white border border-rule p-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Dari</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="border-2 border-rule px-3 py-1.5 font-mono text-sm">
    </div>
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Sampai</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="border-2 border-rule px-3 py-1.5 font-mono text-sm">
    </div>
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Guru</label>
        <select name="staff_id" class="border-2 border-rule px-3 py-1.5 font-serif text-sm">
            <option value="">Semua</option>
            @foreach($staffs as $s)<option value="{{ $s->id }}" {{ request('staff_id')==$s->id?'selected':'' }}>{{ $s->user?->name }}</option>@endforeach
        </select>
    </div>
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Mapel</label>
        <select name="subject_id" class="border-2 border-rule px-3 py-1.5 font-serif text-sm">
            <option value="">Semua</option>
            @foreach($subjects as $s)<option value="{{ $s->id }}" {{ request('subject_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
        </select>
    </div>
    <button class="btn-elite text-xs">Filter</button>
    @if(request()->hasAny(['date_from','date_to','staff_id','subject_id']))<a href="{{ route('admin.teaching-journal.index') }}" class="text-xs ink-secondary underline ml-2">Reset</a>@endif
</form>

{{-- CREATE --}}
<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tulis Jurnal</summary>
    <form method="POST" action="{{ route('admin.teaching-journal.store') }}" class="px-5 py-4 border-t border-rule grid md:grid-cols-3 gap-2">@csrf
        <input type="date" name="date" required value="{{ now()->toDateString() }}" class="border-2 border-rule px-3 py-2 font-mono text-sm">
        <select name="staff_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— guru (opsional) —</option>
            @foreach($staffs as $s)<option value="{{ $s->id }}">{{ $s->user?->name }}</option>@endforeach
        </select>
        <select name="subject_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— mapel —</option>
            @foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
        </select>

        <select name="class_room_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— kelas —</option>
            @foreach($classRooms as $cr)<option value="{{ $cr->id }}">{{ $cr->name }}</option>@endforeach
        </select>
        <select name="class_section_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— rombel (opsional) —</option>
            @foreach($classSections as $cs)<option value="{{ $cs->id }}">{{ $cs->classRoom?->name }} {{ $cs->section?->name }}</option>@endforeach
        </select>
        <select name="class_number" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— jam ke- —</option>
            @foreach(range(1,8) as $n)<option value="{{ $n }}">{{ $n }}</option>@endforeach
        </select>

        <input type="text" name="topic" placeholder="Topik / Materi" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
        <input type="text" name="material" placeholder="Materi pembelajaran" class="md:col-span-3 border-2 border-rule px-3 py-2 font-serif text-sm">

        <div class="md:col-span-3">
            <label class="elite-kicker text-[.6rem] block mb-1">Kompetensi (CP/TP/ATP)</label>
            <div class="max-h-32 overflow-y-auto border border-rule p-2 space-y-1">
                @foreach($competencies as $c)
                <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="competency_ids[]" value="{{ $c->id }}" class="w-3.5 h-3.5"> <span class="font-mono">{{ $c->code }}</span> <span class="text-gray-500">{{ Str::limit($c->description, 50) }} ({{ strtoupper($c->level_type) }})</span></label>
                @endforeach
            </div>
        </div>

        <textarea name="activity" rows="2" placeholder="Aktivitas pembelajaran" class="md:col-span-3 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        <textarea name="attendance_summary" rows="2" placeholder="Ringkasan kehadiran" class="border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        <textarea name="student_participation" rows="2" placeholder="Partisipasi siswa" class="border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        <textarea name="homework" rows="2" placeholder="Pekerjaan rumah (PR)" class="border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        <textarea name="notes" rows="2" placeholder="Catatan" class="md:col-span-3 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        <textarea name="reflection" rows="2" placeholder="Refleksi" class="md:col-span-3 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>

        <div class="md:col-span-3"><button class="btn-elite">Simpan Jurnal</button></div>
    </form>
</details>

{{-- LIST --}}
<div class="space-y-3">
    @forelse($journals as $j)
    <div class="bg-white border border-rule p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="font-serif font-semibold">
                    {{ $j->subject?->name }}
                    @if($j->classRoom) · {{ $j->classRoom->name }}@endif
                    @if($j->classSection) {{ $j->classSection?->classRoom?->name }} {{ $j->classSection?->section?->name }}@endif
                    @if($j->class_number)<span class="text-xs text-gray-400 ml-1">jam ke-{{ $j->class_number }}</span>@endif
                </div>
                <div class="text-xs text-gray-500 mt-0.5">
                    {{ ($j->date ?? $j->journal_date)?->format('d F Y') }}
                    · {{ $j->staff?->user?->name ?? $j->teacher?->name ?? '—' }}
                </div>
            </div>
            <div class="flex gap-2 text-xs shrink-0 items-center">
                @if($j->status === 'draft')
                    <form method="POST" action="{{ route('admin.teaching-journal.publish', $j) }}" class="inline">@csrf
                        <button class="text-green-700 hover:underline">Publikasi</button>
                    </form>
                @else
                    <span class="px-2 py-0.5 bg-green-100 text-green-700 text-[.6rem] font-semibold rounded">Published</span>
                @endif
                <details class="inline-block"><summary class="underline cursor-pointer ink-secondary">Edit</summary>
                    <form method="POST" action="{{ route('admin.teaching-journal.update', $j) }}" class="mt-2 grid gap-1 w-64">@csrf @method('PUT')
                        <input type="text" name="topic" value="{{ $j->topic }}" placeholder="Topik" class="border-2 border-rule px-2 py-1 font-serif text-xs">
                        <textarea name="material" rows="2" class="border-2 border-rule px-2 py-1 font-serif text-xs">{{ $j->material }}</textarea>
                        <textarea name="activity" rows="2" class="border-2 border-rule px-2 py-1 font-serif text-xs">{{ $j->activity ?? $j->learning_activity }}</textarea>
                        <textarea name="reflection" rows="2" placeholder="Refleksi" class="border-2 border-rule px-2 py-1 font-serif text-xs">{{ $j->reflection }}</textarea>
                        <button class="text-xs text-left ink-accent">Simpan</button>
                    </form></details>
                <form method="POST" action="{{ route('admin.teaching-journal.destroy', $j) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-red-700 hover:underline">Hapus</button></form>
            </div>
        </div>
        @if($j->topic)<div class="text-sm mt-2"><b>Topik:</b> {{ $j->topic }}</div>@endif
        @if($j->material)<div class="text-sm mt-1"><b>Materi:</b> {{ $j->material }}</div>@endif
        @if($j->activity ?? $j->learning_activity)<div class="text-sm mt-1"><b>Aktivitas:</b> {{ $j->activity ?? $j->learning_activity }}</div>@endif
        @if($j->student_participation)<div class="text-sm mt-1"><b>Partisipasi:</b> {{ $j->student_participation }}</div>@endif
        @if($j->homework)<div class="text-sm mt-1"><b>PR:</b> {{ $j->homework }}</div>@endif
        @if($j->reflection)<div class="text-sm mt-2 bg-amber-50 border-l-2 border-amber-400 px-3 py-2"><b>Refleksi:</b> {{ $j->reflection }}</div>@endif
    </div>
    @empty
    <div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada jurnal mengajar.</div>
    @endforelse
</div>
<div class="mt-4">{{ $journals->links() }}</div>

@endsection
