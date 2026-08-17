@extends('layouts.parent')
@section('title', 'Kuis')
@section('content')
@include('student-portal._nav')

<div class="mb-7">
<div class="elite-kicker mb-2">Ludus</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Kuis Latihan</h1>
<div class="elite-rule"></div>
</div>

<div class="space-y-3">
@forelse($quizzes as $q)
    <div class="bg-white border border-rule p-5 flex items-center justify-between gap-4">
        <div>
            <div class="font-serif font-semibold text-lg ink-primary">{{ $q->title }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $q->questions_count }} soal · {{ $q->course?->title ?? 'Umum' }}</div>
        </div>
        <a href="{{ route('student.quizzes.take', $q) }}" class="btn-elite" style="padding:.5rem 1.2rem;font-size:.7rem;">Kerjakan</a>
    </div>
@empty
    <div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada kuis.</div>
@endforelse
</div>
@endsection
