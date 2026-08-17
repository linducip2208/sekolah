@extends('layouts.parent')
@section('title', 'Hasil Kuis')
@section('content')
@include('student-portal._nav')

<div class="mb-7">
<div class="elite-kicker mb-2">Ludus</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Hasil: {{ $quiz->title }}</h1>
<div class="elite-rule"></div>
</div>

<div class="grid sm:grid-cols-3 gap-4 mb-7">
    <div class="bg-white border border-rule p-6 text-center">
        <div class="elite-kicker text-[.6rem]">Nilai</div>
        <div class="font-display text-4xl ink-primary mt-2">{{ $result['score'] }}<span class="text-lg text-gray-400">/{{ $result['total'] }}</span></div>
    </div>
    <div class="bg-white border border-rule p-6 text-center">
        <div class="elite-kicker text-[.6rem]">Persentase</div>
        <div class="font-display text-4xl ink-primary mt-2">{{ $result['percent'] }}%</div>
    </div>
    <div class="bg-white border border-rule p-6 text-center">
        <div class="elite-kicker text-[.6rem]">Status</div>
        <div class="font-display text-4xl {{ $result['passed'] ? 'text-green-700' : 'text-red-700' }} mt-2">{{ $result['passed'] ? 'LULUS' : 'BELUM' }}</div>
        <div class="text-xs text-gray-500 mt-1">Percobaan ke-{{ $result['attempt_no'] }}</div>
    </div>
</div>

<div class="bg-white border border-rule overflow-hidden">
    <div class="px-5 py-4 bg-gray-50 border-b border-rule elite-kicker text-[.7rem]">Review Jawaban</div>
    <div class="divide-y divide-rule">
        @foreach($result['feedback'] as $i => $f)
        <div class="px-5 py-4">
            <div class="flex items-start gap-3">
                <span class="font-mono text-xs mt-1 text-gray-500 w-8 shrink-0">{{ $i + 1 }}.</span>
                <div class="flex-1">
                    <div class="font-serif text-sm">{!! $f['question'] !!}</div>
                    <div class="text-xs mt-2 {{ $f['is_correct'] ? 'text-green-700 font-semibold' : 'text-red-700' }}">
                        Jawaban: {{ $f['given_answer'] ?? 'tidak dijawab' }} @if(!$f['is_correct']) · Kunci: {{ $f['correct_answer'] }} @endif
                    </div>
                </div>
                <span class="text-lg shrink-0">{{ $f['is_correct'] ? '✅' : '❌' }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

<a href="{{ route('student.quizzes.index') }}" class="inline-block mt-5 text-sm underline ink-secondary">← Kembali ke daftar kuis</a>
@endsection
