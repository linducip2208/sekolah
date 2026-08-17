@extends('layouts.school-admin')
@section('title', 'Kuis')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Ludus</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Kuis (Latihan)</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">Kuis latihan self-paced dengan feedback instan.</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif

<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Buat Kuis</summary>
    <form method="POST" action="{{ route('admin.quizzes.store') }}" class="px-5 py-4 border-t border-rule grid md:grid-cols-2 gap-2">@csrf
        <input name="title" required maxlength="200" placeholder="Judul kuis" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <select name="course_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— kursus (opsional) —</option>
            @foreach(\App\Models\Lms\Course::where('school_id', auth()->user()->school_id)->orderBy('title')->get() as $c)
                <option value="{{ $c->id }}">{{ $c->title }}</option>
            @endforeach
        </select>
        <input type="number" name="pass_score" min="0" max="100" value="60" placeholder="Pass score %" class="border-2 border-rule px-3 py-2 font-mono text-sm">
        <div class="flex items-center gap-2"><input type="checkbox" name="is_published" value="1" class="w-4 h-4"><span class="text-sm font-serif">Publish</span></div>
        <textarea name="description" rows="2" placeholder="Deskripsi" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        <div class="md:col-span-2"><button class="btn-elite">Buat Kuis</button></div>
    </form>
</details>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white"><tr>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kuis</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kursus</th>
            <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Soal</th>
            <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Percobaan</th>
            <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Status</th>
            <th class="px-4 py-3"></th>
        </tr></thead>
        <tbody>
            @forelse($quizzes as $q)
            <tr class="border-t border-rule hover:bg-gray-50">
                <td class="px-4 py-3 font-serif"><a href="{{ route('admin.quizzes.show', $q) }}" class="ink-secondary hover:underline">{{ $q->title }}</a></td>
                <td class="px-4 py-3 text-xs">{{ $q->course?->title ?? '—' }}</td>
                <td class="px-4 py-3 text-center font-mono text-xs">{{ $q->questions_count }}</td>
                <td class="px-4 py-3 text-center font-mono text-xs">{{ $q->attempts_count }}</td>
                <td class="px-4 py-3 text-center">
                    @if($q->is_published)<span class="text-xs text-green-700">✓ Publish</span>@else<span class="text-xs text-gray-400">Draft</span>@endif
                </td>
                <td class="px-4 py-3 text-right">
                    <form method="POST" action="{{ route('admin.quizzes.destroy', $q) }}" onsubmit="return confirm('Hapus kuis?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada kuis.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
