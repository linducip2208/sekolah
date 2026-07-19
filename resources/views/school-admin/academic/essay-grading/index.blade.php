@extends('layouts.school-admin')
@section('title', 'AI Penilaian Essay')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Assessio Automata</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">AI Penilaian Essay Otomatis</h1>
    <div class="elite-rule"></div>
</div>

<div class="bg-white border border-rule p-5 mb-5">
    <form method="GET" class="grid md:grid-cols-2 gap-3">
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Pilih Ujian</label>
            <select name="exam_id" onchange="this.form.submit()" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <option value="">— Pilih Ujian —</option>
                @foreach($exams as $e)
                    <option value="{{ $e->id }}" @selected($selectedExamId == $e->id)>
                        {{ $e->title }} — {{ $e->classSection?->classRoom?->name }} {{ $e->classSection?->section?->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @if($selectedExamId)
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Pilih Pertanyaan Essay</label>
            <select name="question_id" onchange="this.form.submit()" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <option value="">— Pilih Pertanyaan —</option>
                @foreach($questions as $q)
                    <option value="{{ $q->id }}" @selected($selectedQuestionId == $q->id)>
                        {{ \Str::limit($q->question, 80) }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
    </form>
</div>

@if($selectedExamId && $selectedQuestionId && $questions->isNotEmpty())
    @php $selectedQuestion = $questions->find($selectedQuestionId); @endphp
    @if($selectedQuestion)

    <div class="bg-white border border-rule p-5 mb-5">
        <div class="elite-kicker text-[.6rem] mb-2">Pertanyaan Essay</div>
        <div class="font-serif text-base ink-primary whitespace-pre-wrap">{{ $selectedQuestion->question }}</div>
        @if($selectedQuestion->correct_answer)
            <div class="mt-3 pt-3 border-t border-rule">
                <div class="elite-kicker text-[.6rem] mb-1 text-green-700">Jawaban Referensi</div>
                <div class="text-xs text-gray-700 whitespace-pre-wrap">{{ $selectedQuestion->correct_answer }}</div>
            </div>
        @endif
    </div>

    @if($providers->isNotEmpty())
    <div class="bg-white border border-rule p-3 mb-4 flex flex-wrap items-center gap-3">
        <span class="elite-kicker text-[.6rem] text-gray-500">AI Model:</span>
        <select id="batchModelSelect" class="border-2 border-rule px-3 py-1.5 font-serif text-xs min-w-[200px]">
            <option value="">— Auto (default) —</option>
            @foreach($aiModels as $am)
                <option value="{{ $am->provider?->id }}|{{ $am->id }}">
                    {{ $am->provider?->name }} / {{ $am->display_name ?? $am->model_name }}
                </option>
            @endforeach
        </select>
        <div class="flex-1"></div>
        <button type="button" onclick="submitBatch()" class="btn-elite bg-green-700 border-green-700 text-white text-[.7rem]">
            ✦ Nilai Semua ({{ $students->count() }} Siswa)
        </button>
        <a href="{{ route('admin.academic.essay-grading.export', ['exam_id' => $selectedExamId]) }}"
           class="btn-elite text-[.7rem]" style="background:var(--c-muted);border-color:var(--c-muted);">
            📥 Export CSV
        </a>
    </div>
    @else
    <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 text-xs">
        ⚠ Belum ada AI provider yang aktif. <a href="{{ route('admin.ai.providers.index') }}" class="underline font-semibold">Tambahkan provider AI</a> terlebih dahulu.
    </div>
    @endif

    <form id="batchGradingForm" method="POST" action="{{ route('admin.academic.essay-grading.grade-batch') }}">
    @csrf
    <input type="hidden" name="exam_id" value="{{ $selectedExamId }}">
    <input type="hidden" name="question_text" value="{{ $selectedQuestion->question }}">
    <input type="hidden" name="ai_provider_id" id="batchProviderId" value="">
    <input type="hidden" name="ai_model_id" id="batchModelId" value="">

    <div class="bg-white border border-rule overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-[var(--c-primary)] text-white">
                <tr>
                    <th class="text-center px-2 py-3 elite-kicker text-[.6rem] w-8">#</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Jawaban Siswa</th>
                    <th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Skor AI</th>
                    <th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Level</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Feedback AI</th>
                    <th class="px-3 py-3 w-20"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $i => $student)
                    @php $g = $gradings->get($student->id); @endphp
                    <tr class="border-t border-rule hover:bg-gray-50 {{ $g && $g->ai_score < 40 ? 'bg-red-50' : '' }}">
                        <td class="px-2 py-3 text-center text-[.6rem] text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-3 py-3 font-serif font-semibold text-xs whitespace-nowrap">
                            {{ $student->user?->name ?? "Siswa #{$student->id}" }}
                            <div class="text-[.6rem] text-gray-500">{{ $student->admission_no }}</div>
                        </td>
                        <td class="px-3 py-3 min-w-[280px]">
                            @if($g)
                                <div class="text-xs text-gray-700 line-clamp-2 whitespace-pre-wrap">{{ $g->student_answer }}</div>
                            @endif
                            <textarea name="submissions[{{ $i }}][answer]"
                                      class="w-full border-2 border-rule px-2 py-1.5 font-serif text-xs mt-1"
                                      rows="3"
                                      placeholder="Tempel jawaban siswa di sini...">{{ $g?->student_answer }}</textarea>
                            <input type="hidden" name="submissions[{{ $i }}][student_id]" value="{{ $student->id }}">
                        </td>
                        <td class="px-3 py-3 text-center">
                            @if($g && $g->ai_score !== null)
                                <span class="font-mono font-bold text-lg" style="color:{{ $g->scoreColor() }}">
                                    {{ $g->ai_score }}
                                </span>
                            @else
                                <span class="text-gray-300 italic text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center">
                            @if($g && $g->ai_score !== null)
                                @php
                                    $lvl = $g->scoreLabel();
                                    $cls = match(true){
                                        $g->ai_score >= 80 => 'bg-green-100 text-green-800',
                                        $g->ai_score >= 60 => 'bg-yellow-100 text-yellow-800',
                                        default => 'bg-red-100 text-red-800'
                                    };
                                @endphp
                                <span class="px-2 py-0.5 text-[.6rem] font-medium {{ $cls }}">{{ $lvl }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-xs max-w-[200px]">
                            @if($g && $g->ai_feedback)
                                <details>
                                    <summary class="cursor-pointer text-blue-700 hover:underline text-[.65rem]">Lihat</summary>
                                    <div class="mt-2 p-3 bg-gray-50 border border-rule text-xs leading-relaxed whitespace-pre-wrap">
                                        {{ $g->ai_feedback }}
                                        @if($g->ai_rubric_breakdown)
                                            <div class="mt-2 pt-2 border-t border-rule">
                                                <div class="font-semibold text-[.6rem] mb-1">Rincian Rubrik:</div>
                                                @foreach($g->ai_rubric_breakdown as $k => $v)
                                                    <div class="flex justify-between text-[.6rem]">
                                                        <span class="capitalize">{{ str_replace('_', ' ', $k) }}</span>
                                                        <span class="font-mono">{{ $v }}/100</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                        <div class="mt-1 text-[.55rem] text-gray-400">
                                            {{ $g->tokens_used }} token &middot; {{ $g->processing_time_ms }}ms
                                            &middot; {{ $g->graded_at?->format('d M H:i') }}
                                        </div>
                                    </div>
                                </details>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center whitespace-nowrap">
                            <form method="POST" action="{{ route('admin.academic.essay-grading.grade') }}" class="inline">
                                @csrf
                                <input type="hidden" name="exam_id" value="{{ $selectedExamId }}">
                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                <input type="hidden" name="question_text" value="{{ $selectedQuestion->question }}">
                                <input type="hidden" name="student_answer" class="single-answer-input" value="">
                                <input type="hidden" name="ai_provider_id" class="single-provider-input" value="">
                                <input type="hidden" name="ai_model_id" class="single-model-input" value="">
                                <button type="submit" class="text-xs underline ink-secondary hover:ink-accent"
                                        onclick="setSingleAnswer(this, {{ $i }})">
                                    {{ $g && $g->ai_score !== null ? 'Nilai Ulang' : 'Nilai AI' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Tidak ada siswa di rombel ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </form>
    @endif
@else
    <div class="bg-white border border-rule p-16 text-center">
        <div class="font-display text-5xl mb-4 opacity-30">🤖</div>
        <p class="font-serif text-xl ink-primary mb-2">AI Penilaian Essay Otomatis</p>
        <p class="text-gray-500 text-sm max-w-lg mx-auto">
            Pilih ujian dan pertanyaan essay di atas. Tempelkan jawaban siswa ke textarea,
            lalu gunakan AI untuk menilai secara otomatis dengan skor 0-100, feedback detail,
            dan rincian rubrik per kriteria.
        </p>
        @if($providers->isEmpty())
            <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 text-xs max-w-md mx-auto">
                ⚠ Belum ada AI provider yang aktif. <a href="{{ route('admin.ai.providers.index') }}" class="underline font-semibold">Tambahkan provider AI</a> terlebih dahulu.
            </div>
        @endif
    </div>
@endif

@push('scripts')
<script>
document.getElementById('batchModelSelect')?.addEventListener('change', function() {
    const [pId, mId] = this.value.split('|');
    document.getElementById('batchProviderId').value = pId || '';
    document.getElementById('batchModelId').value = mId || '';
    document.querySelectorAll('.single-provider-input').forEach(el => el.value = pId || '');
    document.querySelectorAll('.single-model-input').forEach(el => el.value = mId || '');
});

function submitBatch() {
    if (!confirm('Nilai semua jawaban essay dengan AI? Proses ini mungkin memakan waktu.')) return;

    const rows = document.querySelectorAll('#batchGradingForm tbody tr');
    let hasAnswer = false;
    rows.forEach(row => {
        const textarea = row.querySelector('textarea');
        if (textarea?.value?.trim()) hasAnswer = true;
    });
    if (!hasAnswer) {
        alert('Isi minimal satu jawaban siswa terlebih dahulu.');
        return;
    }
    document.getElementById('batchGradingForm').submit();
}

function setSingleAnswer(btn, index) {
    const row = btn.closest('tr');
    const textarea = row.querySelector('textarea');
    const answer = textarea?.value?.trim();
    if (!answer) {
        alert('Isi jawaban siswa terlebih dahulu.');
        textarea?.focus();
        return false;
    }
    const form = btn.closest('form');
    form.querySelector('.single-answer-input').value = answer;
    return true;
}
</script>
@endpush
@endsection
