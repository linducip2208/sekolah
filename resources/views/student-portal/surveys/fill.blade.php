@extends('layouts.parent')
@section('title', 'Isi Survei — ' . $template->title)
@section('content')
@include('student-portal._nav')

<div class="max-w-3xl mx-auto px-4 py-8">
    <a href="{{ route('student.surveys') }}" class="text-xs ink-secondary hover:ink-accent">← Kembali</a>
    <div class="elite-card p-6 mt-3">
        <h1 class="font-display text-2xl ink-primary mb-2">{{ $template->title }}</h1>
        <p class="text-sm text-gray-600 font-serif mb-6">{{ $template->description }}</p>

        @if($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('student.surveys.submit', $template) }}">
            @csrf

            @if($targets->isNotEmpty())
                <div class="mb-6 p-4 bg-gray-50 rounded">
                    <label class="elite-kicker text-[.6rem] block mb-2">Pilih yang dievaluasi</label>
                    <select name="target_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" required>
                        <option value="">-- Pilih --</option>
                        @foreach($targets as $t)
                            <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="target_type" value="{{ $template->survey_type === 'guru' ? 'teacher' : 'facility' }}">
                </div>
            @endif

            <div class="space-y-6">
                @foreach($questions as $idx => $q)
                    <div class="p-4 bg-gray-50 rounded">
                        <p class="font-serif font-semibold ink-primary mb-3">
                            <span class="text-xs text-gray-400 mr-2">{{ $idx + 1 }}.</span>
                            {{ $q->question_text }}
                        </p>

                        @if($q->question_type === 'rating_1_5')
                            <div class="flex items-center gap-2" x-data="{ rating: 0 }">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="cursor-pointer text-2xl" @mouseenter="rating = {{ $i }}" @mouseleave="rating = 0">
                                        <input type="radio" name="answers[{{ $idx }}][question_id]" value="{{ $q->id }}" class="hidden" required>
                                        <input type="radio" name="answers[{{ $idx }}][value]" value="{{ $i }}" class="hidden peer" required>
                                        <span class="transition-colors" :class="rating >= {{ $i }} ? 'text-yellow-500' : (document.querySelector(`input[name='answers[{{ $idx }}][value]'][value='{{ $i }}']:checked`) ? 'text-yellow-500' : 'text-gray-300')">
                                            ★
                                        </span>
                                    </label>
                                @endfor
                                <script>
                                    document.querySelectorAll('input[name="answers[{{ $idx }}][value]"]').forEach(r => {
                                        r.addEventListener('change', function() {
                                            let val = parseInt(this.value);
                                            let container = this.closest('.flex');
                                            container.querySelectorAll('span').forEach((s, i) => {
                                                s.className = (i < val) ? 'text-yellow-500' : 'text-gray-300';
                                            });
                                        });
                                    });
                                </script>
                                <span class="text-xs text-gray-400 ml-2">(Pilih 1-5)</span>
                            </div>
                        @elseif($q->question_type === 'text')
                            <input type="hidden" name="answers[{{ $idx }}][question_id]" value="{{ $q->id }}">
                            <textarea name="answers[{{ $idx }}][value]" rows="3" required
                                      class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"
                                      placeholder="Tulis jawaban Anda..."></textarea>
                        @elseif($q->question_type === 'multiple_choice')
                            <div class="space-y-2">
                                @foreach($q->options as $optIdx => $opt)
                                    <label class="flex items-center gap-2 cursor-pointer p-2 hover:bg-white rounded">
                                        <input type="radio" name="answers[{{ $idx }}][value]" value="{{ $opt }}" required class="text-accent">
                                        <input type="hidden" name="answers[{{ $idx }}][question_id]" value="{{ $q->id }}">
                                        <span class="text-sm font-serif">{{ $opt }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6 text-right">
                <button type="submit" class="btn-elite-gold">Kirim Survei</button>
            </div>
        </form>
    </div>
</div>

@stop
