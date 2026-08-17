@extends('layouts.parent')
@section('title', 'Kuis: ' . $quiz->title)
@section('content')

<div class="mb-4">
    <div class="elite-kicker mb-1">Ludus</div>
    <h1 class="elite-h1 text-xl ink-primary">{{ $quiz->title }}</h1>
    <p class="text-xs text-gray-500">{{ $quiz->questions->count() }} soal</p>
</div>

<form method="POST" action="{{ route('student.quizzes.submit', $quiz) }}">@csrf
    @foreach($quiz->questions as $i => $q)
    <div class="bg-white border border-rule p-6 mb-4">
        <div class="elite-kicker text-[.65rem] mb-3">Soal {{ $i + 1 }}</div>
        <div class="font-serif text-lg ink-primary mb-4">{!! $q->question !!}</div>

        @if($q->type === 'mcq')
            <div class="space-y-2">
                @foreach($q->options ?? [] as $opt)
                @php $text = is_array($opt) ? ($opt['text'] ?? '') : $opt; @endphp
                <label class="flex items-start gap-3 p-3 border border-rule hover:bg-gray-50 cursor-pointer">
                    <input type="radio" name="answers[{{ $q->id }}]" value="{{ $text }}" class="mt-1">
                    <span class="font-serif text-sm">{{ $text }}</span>
                </label>
                @endforeach
            </div>
        @else
            <div class="space-y-2">
                @foreach([['v'=>'true','l'=>'Benar'], ['v'=>'false','l'=>'Salah']] as $tf)
                <label class="flex items-start gap-3 p-3 border border-rule hover:bg-gray-50 cursor-pointer">
                    <input type="radio" name="answers[{{ $q->id }}]" value="{{ $tf['v'] }}" class="mt-1">
                    <span class="font-serif text-sm">{{ $tf['l'] }}</span>
                </label>
                @endforeach
            </div>
        @endif
    </div>
    @endforeach

    <button class="btn-elite w-full" style="padding:.7rem;">Kumpulkan & Lihat Hasil</button>
</form>

@endsection
