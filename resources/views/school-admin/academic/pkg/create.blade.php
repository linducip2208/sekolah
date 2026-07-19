@extends('layouts.school-admin')
@section('title', 'Buat PKG')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Pengajaran</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Buat Penilaian Kinerja Guru</h1>
    <div class="elite-rule"></div>
</div>

<form method="POST" action="{{ route('admin.pkg.store') }}" class="max-w-5xl">@csrf
    <div class="elite-card p-6 mb-6 grid md:grid-cols-2 gap-4">
        <div>
            <label class="elite-kicker text-[.55rem] block mb-1">Guru yang Dinilai *</label>
            <select name="teacher_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm w-full">
                <option value="">— Pilih Guru —</option>
                @foreach($teachers as $t)<option value="{{ $t->user?->id }}">{{ $t->user?->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="elite-kicker text-[.55rem] block mb-1">Tahun Ajaran</label>
            <select name="academic_year_id" class="border-2 border-rule px-3 py-2 font-serif text-sm w-full">
                <option value="">— Pilih —</option>
                @foreach($academicYears as $ay)<option value="{{ $ay->id }}">{{ $ay->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="elite-kicker text-[.55rem] block mb-1">Semester *</label>
            <select name="semester" required class="border-2 border-rule px-3 py-2 font-serif text-sm w-full">
                <option value="1">Semester 1</option>
                <option value="2">Semester 2</option>
            </select>
        </div>
        <div>
            <label class="elite-kicker text-[.55rem] block mb-1">Tipe Penilaian *</label>
            <select name="type" required class="border-2 border-rule px-3 py-2 font-serif text-sm w-full">
                <option value="supervisor">Kepala Sekolah / Pengawas</option>
                <option value="self">Self Assessment</option>
                <option value="peer">Peer Review</option>
            </select>
        </div>
        <div>
            <label class="elite-kicker text-[.55rem] block mb-1">Tanggal Penilaian *</label>
            <input type="date" name="assessment_date" required value="{{ date('Y-m-d') }}" class="border-2 border-rule px-3 py-2 text-sm w-full">
        </div>
        <div>
            <label class="elite-kicker text-[.55rem] block mb-1">Catatan Umum</label>
            <textarea name="notes" rows="2" class="border-2 border-rule px-3 py-2 font-serif text-sm w-full" placeholder="Opsional..."></textarea>
        </div>
    </div>

    {{-- Observation (Opsional) --}}
    <details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">Observasi Kelas (Opsional)</summary>
    <div class="px-5 py-5 border-t border-rule grid md:grid-cols-2 gap-3">
        <div>
            <label class="text-xs text-gray-600 block mb-1">Tanggal Observasi</label>
            <input type="date" name="observation_date" class="border-2 border-rule px-3 py-2 text-sm w-full">
        </div>
        <div>
            <label class="text-xs text-gray-600 block mb-1">Rombel</label>
            <select name="class_section_id" class="border-2 border-rule px-3 py-2 text-sm w-full">
                <option value="">— Pilih —</option>
                @foreach(\App\Models\Academic\ClassSection::where('school_id', auth()->user()->school_id)->with(['classRoom','section'])->get() as $cs)
                <option value="{{ $cs->id }}">{{ $cs->classRoom?->name }} {{ $cs->section?->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs text-gray-600 block mb-1">Mata Pelajaran</label>
            <select name="subject_id" class="border-2 border-rule px-3 py-2 text-sm w-full">
                <option value="">— Pilih —</option>
                @foreach(\App\Models\Academic\Subject::where('school_id', auth()->user()->school_id)->get() as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="text-xs text-gray-600 block mb-1">Catatan Observasi</label>
            <textarea name="observation_notes" rows="2" class="border-2 border-rule px-3 py-2 font-serif text-sm w-full"></textarea>
        </div>
        <div>
            <label class="text-xs text-gray-600 block mb-1">Suasana Kelas</label>
            <select name="class_atmosphere" class="border-2 border-rule px-3 py-2 text-sm w-full">
                <option value="">— Pilih —</option>
                <option value="sangat_kondusif">Sangat Kondusif</option>
                <option value="kondusif">Kondusif</option>
                <option value="cukup_kondusif">Cukup Kondusif</option>
                <option value="kurang_kondusif">Kurang Kondusif</option>
            </select>
        </div>
        <div>
            <label class="text-xs text-gray-600 block mb-1">Keterlibatan Siswa</label>
            <select name="student_engagement" class="border-2 border-rule px-3 py-2 text-sm w-full">
                <option value="">— Pilih —</option>
                <option value="sangat_aktif">Sangat Aktif</option>
                <option value="aktif">Aktif</option>
                <option value="cukup_aktif">Cukup Aktif</option>
                <option value="pasif">Pasif</option>
            </select>
        </div>
    </div>
    </details>

    {{-- 14 Kompetensi Scoring --}}
    <h2 class="elite-h3 text-xl ink-primary mb-4 ornament">Skor 14 Kompetensi Guru</h2>
    <p class="font-serif text-sm text-gray-600 mb-4">Beri skor 0–100 untuk setiap kompetensi. Kosongkan jika tidak dinilai. Sertakan bukti/evidence.</p>

    <div class="space-y-4 mb-6">
        @foreach($competencies->groupBy('competency_type') as $type => $comps)
        <div class="elite-card p-5">
            <div class="elite-kicker text-[.6rem] mb-3">{{ strtoupper($type) }} ({{ $comps->count() }} Kompetensi)</div>
            @foreach($comps as $comp)
            <details class="mb-3 border border-rule p-3" {{ $loop->first ? 'open' : '' }}>
            <summary class="cursor-pointer font-serif font-semibold text-sm ink-primary">
                <span class="font-mono text-xs text-gray-500 mr-2">{{ $comp->code }}</span>
                {{ $comp->name }}
                <span class="text-xs text-gray-400 ml-2">(Bobot: {{ $comp->weight }})</span>
            </summary>
            <div class="mt-2 text-xs text-gray-600 font-serif italic">{{ $comp->description }}</div>
            <div class="mt-3 grid md:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs text-gray-600 block mb-1">Skor (0–100)</label>
                    <input type="number" name="scores[{{ $comp->id }}]" min="0" max="100" step="1" class="border-2 border-rule px-3 py-2 font-mono text-sm w-full" placeholder="...">
                </div>
                <div>
                    <label class="text-xs text-gray-600 block mb-1">Bukti / Catatan</label>
                    <input type="text" name="evidence_notes[{{ $comp->id }}]" class="border-2 border-rule px-3 py-2 text-sm w-full" placeholder="Dokumen RPP, hasil evaluasi, dll...">
                </div>
            </div>
            </details>
            @endforeach
        </div>
        @endforeach
    </div>

    <div class="flex gap-3 mb-10">
        <button class="btn-elite">Simpan PKG</button>
        <a href="{{ route('admin.pkg.index') }}" class="btn-elite-ghost">Batal</a>
    </div>
</form>
@endsection
