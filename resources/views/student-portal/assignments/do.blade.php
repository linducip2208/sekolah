@extends('layouts.school-admin')
@section('title', 'Kerjakan Tugas')
@section('sidebar')
<nav>
    <a href="{{ route('student.dashboard') }}" class="sidebar-link">Dashboard</a>
    <a href="{{ route('student.assignments') }}" class="sidebar-link active">Tugas</a>
</nav>
@endsection
@section('content')
<a href="{{ route('student.assignments') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kembali ke Daftar Tugas</a>
<div class="mb-7"><div class="elite-kicker mb-2">Akademia</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">{{ $assignment->title }}</h1><div class="elite-rule"></div></div>

@if($existingSubmission)
<div class="elite-card p-6 bg-green-50 border-green-300 mb-6 text-center">
    <div class="font-display text-2xl font-bold text-green-700 mb-2">✓ Telah Dikumpulkan</div>
    @if($existingSubmission->marks !== null)
    <div class="font-display text-3xl font-bold ink-accent">{{ $existingSubmission->marks }}/{{ $assignment->total_marks }}</div>
    @if($existingSubmission->feedback)<p class="font-serif text-sm text-gray-600 mt-2 italic">"{{ $existingSubmission->feedback }}"</p>@endif
    @else
    <p class="font-serif text-sm text-gray-600">Menunggu dinilai oleh guru.</p>
    @endif
</div>
@else
<div class="elite-card p-5 mb-6 grid md:grid-cols-2 gap-3 text-sm">
    <div><span class="elite-kicker text-[.55rem]">Mata Pelajaran</span><br><span class="font-serif">{{ $assignment->lesson?->subject?->name }}</span></div>
    <div><span class="elite-kicker text-[.55rem]">Nilai Max</span><br><span class="font-mono font-bold">{{ $assignment->total_marks }}</span></div>
    <div class="md:col-span-2"><span class="elite-kicker text-[.55rem]">Batas Waktu</span><br><span class="{{ $assignment->due_date && now()->gt($assignment->due_date) ? 'text-red-700' : '' }}">{{ $assignment->due_date?->format('d M Y, H:i') ?? 'Tidak ada' }}</span></div>
    @if($assignment->instructions)<div class="md:col-span-2"><span class="elite-kicker text-[.55rem]">Instruksi</span><br><p class="font-serif italic">{{ $assignment->instructions }}</p></div>@endif
</div>

<form method="POST" action="{{ route('student.assignments.submit', $assignment) }}" enctype="multipart/form-data">
@csrf

{{-- MCQ Questions --}}
@if($assignment->questions->isNotEmpty())
<div class="space-y-6 mb-6">
    <h3 class="elite-h3 text-lg ink-primary ornament-center">Soal</h3>
    @foreach($assignment->questions as $q)
    <div class="elite-card p-5">
        <div class="elite-kicker text-[.55rem] mb-2">Soal #{{ $q->question_number }} ({{ $q->points }} poin)</div>
        <p class="font-serif text-base ink-primary mb-3">{{ $q->question_text }}</p>

        @if($q->question_type === 'mcq')
            @foreach($q->options ?? [] as $i => $opt)
            @php $letter = chr(65 + $i); @endphp
            <label class="flex items-center gap-3 py-2 px-3 border border-rule mb-1 cursor-pointer hover:bg-gray-50">
                <input type="radio" name="answers[{{ $q->question_number }}]" value="{{ $letter }}" class="w-4 h-4" style="accent-color:var(--c-accent)">
                <span class="font-serif text-sm">{{ $letter }}. {{ $opt }}</span>
            </label>
            @endforeach
        @elseif($q->question_type === 'essay')
            <textarea name="answers[{{ $q->question_number }}]" rows="4" class="border-2 border-rule px-3 py-2 font-serif text-sm w-full" placeholder="Tulis jawaban Anda..."></textarea>
        @elseif($q->question_type === 'short_answer')
            <input type="text" name="answers[{{ $q->question_number }}]" class="border-2 border-rule px-3 py-2 font-serif text-sm w-full" placeholder="Jawaban singkat...">
        @elseif($q->question_type === 'file_upload')
            <input type="file" name="answers[{{ $q->question_number }}]" class="border-2 border-rule px-3 py-2 text-sm w-full">
        @endif
    </div>
    @endforeach
</div>
@else
{{-- Essay-only: single textarea --}}
<div class="elite-card p-5 mb-6">
    <label class="elite-kicker text-[.55rem] block mb-2">Jawaban Anda</label>
    <textarea name="answers[1]" rows="8" class="border-2 border-rule px-3 py-2 font-serif text-sm w-full" placeholder="Tulis jawaban Anda..."></textarea>
</div>
@endif

{{-- File Upload (Essay mode) --}}
<div class="elite-card p-5 mb-6">
    <label class="elite-kicker text-[.55rem] block mb-2">Upload File (PDF/DOCX/IMG, max {{ $assignment->max_file_size_mb ?? 10 }} MB)</label>
    <input type="file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="border-2 border-rule px-3 py-2 text-sm w-full">
</div>

<div class="flex gap-3 mb-10">
    <button class="btn-elite">Kumpulkan Tugas</button>
    <a href="{{ route('student.assignments') }}" class="btn-elite-ghost">Batal</a>
</div>
</form>
@endif
@endsection
