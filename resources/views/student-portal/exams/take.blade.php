@extends('layouts.parent')
@section('title', 'Ujian: ' . $exam->title)
@section('content')

<div class="mb-4" x-data="examRunner" x-init="init()">
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="elite-kicker mb-1">CBT</div>
            <h1 class="elite-h1 text-xl ink-primary">{{ $exam->title }}</h1>
            <p class="text-xs text-gray-500">{{ $exam->subject?->name }} · {{ $exam->total_marks }} poin</p>
        </div>
        <div class="text-right">
            <div class="elite-kicker text-[.6rem] text-gray-500">Sisa Waktu</div>
            <div class="font-display text-3xl ink-primary" x-text="timeLeft" :class="{ 'text-red-700': seconds < 300 }"></div>
        </div>
    </div>

    <div class="grid lg:grid-cols-4 gap-5">
        <div class="lg:col-span-3">
            <form id="exam-form" method="POST" action="{{ route('student.exams.submit', $exam) }}">
                @csrf
                @foreach($exam->questions as $i => $q)
                <div class="bg-white border border-rule p-6" x-show="current === {{ $i }}" x-cloak>
                    <div class="flex items-center justify-between mb-4">
                        <span class="elite-kicker text-[.65rem]">Soal {{ $i + 1 }} / {{ $exam->questions->count() }}</span>
                        <button type="button" @click="flags[{{ $q->id }}] = !flags[{{ $q->id }}]"
                            class="text-xs px-2 py-1 rounded" :class="flags[{{ $q->id }}] ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600'">
                            <span x-text="flags[{{ $q->id }}] ? '⚠ Ragu-ragu' : 'Tandai ragu-ragu'"></span>
                        </button>
                    </div>

                    <div class="font-serif text-lg ink-primary mb-5">{!! $q->question !!}</div>

                    @if($q->type === 'mcq')
                        <div class="space-y-2">
                            @foreach($q->options ?? [] as $opt)
                            @php $text = is_array($opt) ? ($opt['text'] ?? '') : $opt; @endphp
                            <label class="flex items-start gap-3 p-3 border border-rule hover:bg-gray-50 cursor-pointer">
                                <input type="radio" name="answers[{{ $q->id }}]" value="{{ $text }}"
                                    x-model="answers[{{ $q->id }}]" class="mt-1">
                                <span class="font-serif text-sm">{{ $text }}</span>
                            </label>
                            @endforeach
                        </div>
                    @elseif($q->type === 'true_false')
                        <div class="space-y-2">
                            @foreach([['v'=>'true','l'=>'Benar'], ['v'=>'false','l'=>'Salah']] as $tf)
                            <label class="flex items-start gap-3 p-3 border border-rule hover:bg-gray-50 cursor-pointer">
                                <input type="radio" name="answers[{{ $q->id }}]" value="{{ $tf['v'] }}"
                                    x-model="answers[{{ $q->id }}]" class="mt-1">
                                <span class="font-serif text-sm">{{ $tf['l'] }}</span>
                            </label>
                            @endforeach
                        </div>
                    @else
                        <textarea name="answers[{{ $q->id }}]" rows="6" x-model="answers[{{ $q->id }}]"
                            placeholder="Tulis jawaban Anda..." class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
                    @endif
                </div>
                @endforeach
            </form>

            <div class="flex justify-between mt-4">
                <button type="button" @click="prev()" class="btn-elite" x-show="current > 0" style="padding:.5rem 1.2rem;font-size:.7rem;">← Sebelumnya</button>
                <button type="button" @click="next()" class="btn-elite" x-show="current < total - 1" style="padding:.5rem 1.2rem;font-size:.7rem;">Selanjutnya →</button>
            </div>
        </div>

        <div class="bg-white border border-rule p-4 h-fit">
            <div class="elite-kicker text-[.65rem] mb-3">Navigasi Soal</div>
            <div class="grid grid-cols-5 gap-2 mb-4">
                @foreach($exam->questions as $i => $q)
                <button type="button" @click="current = {{ $i }}"
                    class="py-2 text-xs font-mono border"
                    :class="{
                        'bg-[var(--c-primary)] text-white': current === {{ $i }},
                        'bg-green-100 border-green-300': current !== {{ $i }} && answers[{{ $q->id }}],
                        'bg-amber-100 border-amber-300': flags[{{ $q->id }}] && !answers[{{ $q->id }}],
                        'bg-white border-rule': !answers[{{ $q->id }}] && !flags[{{ $q->id }}] && current !== {{ $i }}
                    }">{{ $i + 1 }}</button>
                @endforeach
            </div>

            <div class="text-xs space-y-1 mb-4 text-gray-500">
                <div><span class="inline-block w-3 h-3 bg-green-100 border border-green-300 mr-1"></span>Sudah dijawab</div>
                <div><span class="inline-block w-3 h-3 bg-amber-100 border border-amber-300 mr-1"></span>Ragu-ragu</div>
                <div><span class="inline-block w-3 h-3 bg-white border border-rule mr-1"></span>Belum dijawab</div>
            </div>

            <div class="text-xs text-gray-500 mb-2">
                Terjawab: <span x-text="answeredCount"></span> / {{ $exam->questions->count() }}
            </div>

            <button type="button" @click="submitExam()" class="btn-elite w-full" style="padding:.6rem;font-size:.7rem;">Kumpulkan Jawaban</button>
        </div>
    </div>
</div>

<script>
function examRunner() {
    return {
        current: 0,
        total: {{ $exam->questions->count() }},
        answers: {},
        flags: {},
        deadline: new Date('{{ $deadline?->toIso8601String() }}').getTime(),
        seconds: 0,
        timeLeft: '',
        timer: null,

        init() {
            this.tick();
            this.timer = setInterval(() => this.tick(), 1000);
        },

        tick() {
            this.seconds = Math.max(0, Math.floor((this.deadline - Date.now()) / 1000));
            const m = Math.floor(this.seconds / 60);
            const s = this.seconds % 60;
            this.timeLeft = m + ':' + String(s).padStart(2, '0');
            if (this.seconds <= 0) {
                clearInterval(this.timer);
                this.submitExam();
            }
        },

        get answeredCount() {
            return Object.values(this.answers).filter(v => v !== undefined && v !== null && v !== '').length;
        },

        next() { if (this.current < this.total - 1) this.current++; },
        prev() { if (this.current > 0) this.current--; },

        submitExam() {
            if (!confirm('Yakin ingin mengumpulkan jawaban?')) return;
            document.getElementById('exam-form').submit();
        }
    };
}
</script>
@endsection
