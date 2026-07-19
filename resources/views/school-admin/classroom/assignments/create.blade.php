@extends('layouts.school-admin')
@section('title', isset($assignment) ? 'Edit Tugas' : 'Buat Tugas Baru')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Operationes</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">{{ isset($assignment) ? 'Edit Tugas' : 'Buat Tugas Baru' }}</h1><div class="elite-rule"></div></div>

<form method="POST" action="{{ isset($assignment) ? route('admin.assignments.update', $assignment) : route('admin.assignments.store') }}" x-data="questionBuilder({{ isset($assignment) ? json_encode($assignment->questions) : '[]' }})" class="max-w-5xl">@csrf
    @if(isset($assignment)) @method('PUT') @endif

    <div class="elite-card p-6 mb-6 grid md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="elite-kicker text-[.55rem] block mb-1">Judul Tugas *</label>
            <input type="text" name="title" required maxlength="255" value="{{ old('title', $assignment->title ?? '') }}" class="border-2 border-rule px-3 py-2 font-serif text-sm w-full" placeholder="Ulangan Harian Bab 3...">
        </div>
        <div>
            <label class="elite-kicker text-[.55rem] block mb-1">Materi *</label>
            <select name="lesson_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm w-full">
                <option value="">— Pilih Materi —</option>
                @foreach($lessons as $l)<option value="{{ $l->id }}" {{ old('lesson_id', $assignment->lesson_id ?? '') == $l->id ? 'selected' : '' }}>{{ $l->title }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="elite-kicker text-[.55rem] block mb-1">Batas Waktu *</label>
            <input type="datetime-local" name="due_date" required value="{{ old('due_date', isset($assignment) ? $assignment->due_date?->format('Y-m-d\TH:i') : '') }}" class="border-2 border-rule px-3 py-2 text-sm w-full">
        </div>
        <div>
            <label class="elite-kicker text-[.55rem] block mb-1">Total Nilai *</label>
            <input type="number" name="total_marks" min="1" max="1000" required value="{{ old('total_marks', $assignment->total_marks ?? 100) }}" class="border-2 border-rule px-3 py-2 font-mono text-sm w-full">
        </div>
        <div>
            <label class="elite-kicker text-[.55rem] block mb-1">Tipe Soal *</label>
            <select name="question_type" required class="border-2 border-rule px-3 py-2 font-serif text-sm w-full" x-model="questionType">
                <option value="essay" {{ (old('question_type', $assignment->question_type ?? '') == 'essay') ? 'selected' : '' }}>Essay (teks panjang)</option>
                <option value="multiple_choice" {{ (old('question_type', $assignment->question_type ?? '') == 'multiple_choice') ? 'selected' : '' }}>Pilihan Ganda</option>
                <option value="mixed" {{ (old('question_type', $assignment->question_type ?? '') == 'mixed') ? 'selected' : '' }}>Campuran</option>
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="elite-kicker text-[.55rem] block mb-1">Instruksi / Petunjuk</label>
            <textarea name="instructions" rows="3" class="border-2 border-rule px-3 py-2 font-serif text-sm w-full">{{ old('instructions', $assignment->instructions ?? '') }}</textarea>
        </div>
        <div class="flex items-center gap-6">
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="allow_late_submission" value="1" {{ old('allow_late_submission', $assignment->allow_late_submission ?? false) ? 'checked' : '' }}> Izinkan telat mengumpulkan</label>
            <div>
                <label class="text-xs text-gray-600">Max ukuran file (MB)</label>
                <input type="number" name="max_file_size_mb" value="{{ old('max_file_size_mb', $assignment->max_file_size_mb ?? 10) }}" class="border-2 border-rule px-3 py-2 w-20 text-sm">
            </div>
        </div>
    </div>

    {{-- MCQ / Mixed Builder --}}
    <div x-show="questionType === 'multiple_choice' || questionType === 'mixed'" class="mb-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="elite-h3 text-lg ink-primary">Daftar Soal</h3>
            <button type="button" @click="addQuestion()" class="btn-elite-gold text-xs">+ Tambah Soal</button>
        </div>

        <template x-for="(q, idx) in questions" :key="idx">
            <div class="elite-card p-4 mb-3">
                <div class="flex justify-between items-center mb-3">
                    <div class="elite-kicker text-[.6rem]">Soal #<span x-text="idx + 1"></span></div>
                    <button type="button" @click="removeQuestion(idx)" class="text-xs text-red-700 hover:underline">✕ Hapus</button>
                </div>
                <div class="grid gap-3">
                    <textarea :name="'questions['+idx+'][question_text]'" required rows="2" class="border-2 border-rule px-3 py-2 font-serif text-sm w-full" placeholder="Teks pertanyaan..." x-model="q.question_text"></textarea>
                    <div class="grid md:grid-cols-2 gap-3">
                        <select :name="'questions['+idx+'][question_type]'" class="border-2 border-rule px-3 py-2 text-sm w-full" x-model="q.question_type">
                            <option value="mcq">Pilihan Ganda</option>
                            <option value="essay">Essay</option>
                            <option value="short_answer">Jawaban Singkat</option>
                            <option value="file_upload">Upload File</option>
                        </select>
                        <input type="number" :name="'questions['+idx+'][points]'" min="1" max="100" class="border-2 border-rule px-3 py-2 font-mono text-sm w-full" placeholder="Poin" x-model="q.points">
                    </div>
                    <div x-show="q.question_type === 'mcq'">
                        <label class="text-xs text-gray-600 block mb-1">Opsi (satu per baris, opsi pertama = A, dst)</label>
                        <textarea :name="'questions['+idx+'][options]'" rows="4" class="border-2 border-rule px-3 py-2 font-mono text-xs w-full" placeholder="Opsi A&#10;Opsi B&#10;Opsi C&#10;Opsi D" x-model="q.options"></textarea>
                    </div>
                    <div x-show="q.question_type === 'mcq' || q.question_type === 'short_answer'">
                        <input type="text" :name="'questions['+idx+'][correct_answer]'" class="border-2 border-rule px-3 py-2 text-sm w-full" placeholder="Jawaban benar (A/B/C/D atau kata kunci)" x-model="q.correct_answer">
                    </div>
                </div>
            </div>
        </template>

        <div x-show="questions.length === 0" class="text-center text-gray-500 italic font-serif py-6">Belum ada soal. Klik "Tambah Soal" di atas.</div>
    </div>

    <div class="flex gap-3 mb-10">
        <button class="btn-elite">{{ isset($assignment) ? 'Update Tugas' : 'Simpan Tugas' }}</button>
        <a href="{{ route('admin.assignments.index') }}" class="btn-elite-ghost">Batal</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
function questionBuilder(existing) {
    return {
        questionType: '{{ old('question_type', $assignment->question_type ?? 'essay') }}',
        questions: existing && existing.length ? existing.map(q => ({
            question_text: q.question_text || '',
            question_type: q.question_type || 'mcq',
            options: q.options ? q.options.join('\n') : '',
            correct_answer: q.correct_answer || '',
            points: q.points || 10,
        })) : [],
        addQuestion() {
            this.questions.push({
                question_text: '',
                question_type: 'mcq',
                options: '',
                correct_answer: '',
                points: 10,
            });
        },
        removeQuestion(idx) {
            this.questions.splice(idx, 1);
        },
    };
}
</script>
@endpush
