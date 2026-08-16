@extends('layouts.school-admin')
@section('title', 'Ujian')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Examina</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Ujian</h1>
    <div class="elite-rule"></div>
</div>

<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Buat Ujian Baru</summary>
    <form method="POST" action="{{ route('admin.exams.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-3 gap-3">
        @csrf
        <div class="md:col-span-3">
            <label class="elite-kicker text-[.6rem] block mb-1">Judul</label>
            <input name="title" required maxlength="255" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="UTS Matematika Kelas 10">
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Rombel</label>
            <select name="class_section_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <option value="">— pilih —</option>
                @foreach($classSections as $cs)<option value="{{ $cs->id }}">{{ $cs->classRoom?->name }} {{ $cs->section?->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Mapel</label>
            <select name="subject_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <option value="">— pilih —</option>
                @foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Tipe</label>
            <select name="type" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <option value="offline">Offline (kertas)</option>
                <option value="online">Online (digital)</option>
            </select>
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Mulai</label>
            <input type="datetime-local" name="start_at" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Selesai</label>
            <input type="datetime-local" name="end_at" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Durasi (menit)</label>
            <input type="number" min="1" max="600" name="duration_minutes" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Total Nilai</label>
            <input type="number" min="1" max="1000" name="total_marks" required value="100" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Nilai Lulus</label>
            <input type="number" min="0" name="pass_marks" required value="60" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
        </div>
        <div class="md:col-span-3">
            <button class="btn-elite">Buat Ujian</button>
        </div>
    </form>
</details>

<form method="GET" class="bg-white border border-rule p-4 mb-4 flex gap-2">
    <select name="class_section_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">— Semua Rombel —</option>
        @foreach($classSections as $cs)
            <option value="{{ $cs->id }}" @selected(request('class_section_id') == $cs->id)>{{ $cs->classRoom?->name }} {{ $cs->section?->name }}</option>
        @endforeach
    </select>
    <button class="btn-elite" style="padding:.5rem 1rem;font-size:.65rem;">Filter</button>
</form>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Judul</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Rombel</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Mapel</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Mulai</th>
                <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Total/Lulus</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tipe</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($exams as $e)
                <tr class="border-t border-rule hover:bg-gray-50">
                    <td class="px-4 py-3 font-serif font-semibold">{{ $e->title }}</td>
                    <td class="px-4 py-3 text-xs">{{ $e->classSection?->classRoom?->name }} {{ $e->classSection?->section?->name }}</td>
                    <td class="px-4 py-3 text-xs">{{ $e->subject?->name }}</td>
                    <td class="px-4 py-3 text-xs">{{ $e->start_at?->format('d M Y H:i') ?? '—' }}</td>
                    <td class="px-4 py-3 text-center font-mono text-xs">{{ $e->total_marks }}/{{ $e->pass_marks }}</td>
                    <td class="px-4 py-3"><span class="elite-kicker text-[.55rem]">{{ ucfirst($e->type) }}</span></td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('admin.exams.marks', $e) }}" class="text-xs underline ink-secondary hover:ink-accent">Input Nilai</a>
                        <a href="{{ route('admin.exams.analysis', $e) }}" class="text-xs underline ink-secondary hover:ink-accent ml-2">Analisis Butir</a>
                        <form method="POST" action="{{ route('admin.exams.destroy', $e) }}" class="inline ml-2" onsubmit="return confirm('Hapus?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-700 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada ujian.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $exams->links() }}</div>

@endsection
