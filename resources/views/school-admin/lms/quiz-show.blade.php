@extends('layouts.school-admin')
@section('title', 'Detail Kuis')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.quizzes.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kuis</a>

<div class="mb-7">
    <div class="elite-kicker mb-2">Ludus</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">{{ $quiz->title }}</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">{{ $quiz->description }} · Pass {{ $quiz->pass_score }}% · {{ $quiz->questions->count() }} soal</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif

<div class="grid md:grid-cols-3 gap-6">
    <div class="md:col-span-2">
        <div class="bg-white border border-rule overflow-hidden mb-4">
            <div class="px-4 py-3 bg-gray-50 border-b border-rule elite-kicker text-[.7rem]">Soal Kuis</div>
            <table class="w-full text-sm">
                <tbody>
                    @forelse($quiz->questions as $i => $q)
                    <tr class="border-b border-rule">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500 w-8">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 font-serif text-sm">{{ Str::limit(strip_tags($q->question), 80) }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $q->type }} · kunci: {{ $q->correct_answer }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.quizzes.questions.destroy', $q) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
                        </td>
                    </tr>
                    @empty
                    <tr><td class="p-8 text-center text-gray-400 italic font-serif">Belum ada soal.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <details class="mb-4 bg-white border border-rule">
            <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Soal Manual</summary>
            <form method="POST" action="{{ route('admin.quizzes.questions.store', $quiz) }}" class="px-5 py-4 border-t border-rule grid gap-2">@csrf
                <textarea name="question" rows="2" required placeholder="Pertanyaan" class="border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
                <select name="type" class="border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="mcq">Pilihan Ganda</option>
                    <option value="true_false">Benar/Salah</option>
                </select>
                <textarea name="options" rows="3" placeholder="Opsi (satu per baris) — untuk pilihan ganda" class="border-2 border-rule px-3 py-2 font-mono text-xs"></textarea>
                <input name="correct_answer" required placeholder="Kunci jawaban" class="border-2 border-rule px-3 py-2 font-serif text-sm">
                <button class="btn-elite" style="padding:.4rem 1rem;font-size:.65rem;">Simpan Soal</button>
            </form>
        </details>

        <form method="POST" action="{{ route('admin.quizzes.generate', $quiz) }}" class="bg-white border border-rule p-4 flex gap-2 items-center">
            @csrf
            <input type="number" name="count" min="1" max="100" required value="5" class="border-2 border-rule px-3 py-2 font-mono text-sm w-24">
            <button class="btn-elite" style="padding:.5rem 1rem;font-size:.65rem;">Generate dari Bank Soal</button>
        </form>
    </div>

    <div class="bg-white border border-rule overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b border-rule elite-kicker text-[.7rem]">Percobaan ({{ $quiz->attempts->count() }})</div>
        <table class="w-full text-sm">
            <tbody>
                @forelse($quiz->attempts as $a)
                <tr class="border-b border-rule">
                    <td class="px-3 py-2 text-xs">{{ $a->student?->user?->name }}</td>
                    <td class="px-3 py-2 text-right font-mono text-xs">{{ $a->score }}/{{ $a->total }}</td>
                    <td class="px-3 py-2 text-right text-xs {{ $a->passed ? 'text-green-700' : 'text-red-700' }}">{{ $a->passed ? 'Lulus' : 'Belum' }}</td>
                </tr>
                @empty
                <tr><td class="p-6 text-center text-gray-400 italic font-serif text-xs">Belum ada percobaan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
