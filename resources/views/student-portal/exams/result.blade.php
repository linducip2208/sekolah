@extends('layouts.parent')
@section('title', 'Hasil Ujian')
@section('content')
@include('student-portal._nav')

<div class="mb-7">
<div class="elite-kicker mb-2">CBT</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Hasil: {{ $exam->title }}</h1>
<div class="elite-rule"></div>
</div>

<div class="grid md:grid-cols-3 gap-4 mb-7">
    <div class="bg-white border border-rule p-6 text-center">
        <div class="elite-kicker text-[.6rem]">Nilai</div>
        <div class="font-display text-4xl ink-primary mt-2">{{ $result->obtained_marks }}<span class="text-lg text-gray-400">/{{ $exam->total_marks }}</span></div>
    </div>
    <div class="bg-white border border-rule p-6 text-center">
        <div class="elite-kicker text-[.6rem]">Status</div>
        <div class="font-display text-4xl mt-2 {{ $result->status === 'passed' ? 'text-green-700' : 'text-red-700' }}">{{ $result->status === 'passed' ? 'LULUS' : 'TIDAK LULUS' }}</div>
    </div>
    <div class="bg-white border border-rule p-6 text-center">
        <div class="elite-kicker text-[.6rem]">Batas Lulus</div>
        <div class="font-display text-4xl ink-primary mt-2">{{ $exam->pass_marks }}</div>
    </div>
</div>

<div class="bg-white border border-rule overflow-hidden">
    <div class="px-5 py-4 bg-gray-50 border-b border-rule elite-kicker text-[.7rem]">Review Jawaban</div>
    <div class="divide-y divide-rule">
        @foreach($exam->questions as $i => $q)
            @php $answer = $result->answers[$q->id] ?? null; @endphp
            <div class="px-5 py-4">
                <div class="flex items-start gap-3">
                    <span class="font-mono text-xs mt-1 text-gray-500 w-8 shrink-0">{{ $i + 1 }}.</span>
                    <div class="flex-1">
                        <div class="font-serif text-sm">{!! $q->question !!}</div>
                        <div class="text-xs mt-2">
                            @if($q->type === 'essay')
                                <span class="text-gray-500 italic">Jawaban: {{ $answer ?: '—' }}</span>
                            @else
                                <span class="{{ (string)$answer === (string)$q->correct_answer ? 'text-green-700 font-semibold' : 'text-red-700' }}">
                                    Jawaban: {{ $answer ?? 'tidak dijawab' }}
                                </span>
                                @if((string)$answer !== (string)$q->correct_answer)
                                    <span class="text-gray-500 ml-2">Kunci: {{ $q->correct_answer }}</span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<a href="{{ route('student.exams.index') }}" class="inline-block mt-5 text-sm underline ink-secondary">← Kembali ke daftar ujian</a>
@endsection
