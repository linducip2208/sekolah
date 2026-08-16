@extends('layouts.school-admin')
@section('title', 'Generate Soal dari Bank')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.exams.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Daftar Ujian</a>

<div class="mb-7">
    <div class="elite-kicker mb-2">Generatio</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Generate Soal dari Bank: {{ $exam->title }}</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">Mapel: {{ $exam->subject?->name }} · Tipe: {{ $exam->type === 'online' ? 'Online' : 'Offline' }}</p>
</div>

@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif

<div class="grid md:grid-cols-2 gap-6">
    <form method="POST" action="{{ route('admin.exams.generate.store', $exam) }}" class="bg-white border border-rule p-5">
        @csrf
        <div class="elite-kicker text-[.6rem] mb-3">Komposisi Soal</div>

        <div class="mb-4">
            <label class="elite-kicker text-[.6rem] block mb-1">Kategori (opsional)</label>
            <select name="question_bank_category_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <option value="">— Semua kategori —</option>
                @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
            </select>
        </div>

        <div class="grid grid-cols-3 gap-3">
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1 text-green-700">Mudah</label>
                <input type="number" name="easy" min="0" max="200" value="0" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1 text-amber-700">Sedang</label>
                <input type="number" name="medium" min="0" max="200" value="0" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1 text-red-700">Sulit</label>
                <input type="number" name="hard" min="0" max="200" value="0" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
            </div>
        </div>

        <div class="mt-5">
            <button class="btn-elite">Generate Soal</button>
        </div>
    </form>

    <div class="bg-white border border-rule p-5">
        <div class="elite-kicker text-[.6rem] mb-3">Stok Bank Soal (mapel ini)</div>
        <table class="w-full text-sm">
            <tr class="border-b border-rule">
                <td class="py-2 text-green-700 font-serif">Mudah</td>
                <td class="py-2 text-right font-mono">{{ $bankStats['easy'] ?? 0 }}</td>
            </tr>
            <tr class="border-b border-rule">
                <td class="py-2 text-amber-700 font-serif">Sedang</td>
                <td class="py-2 text-right font-mono">{{ $bankStats['medium'] ?? 0 }}</td>
            </tr>
            <tr class="border-b border-rule">
                <td class="py-2 text-red-700 font-serif">Sulit</td>
                <td class="py-2 text-right font-mono">{{ $bankStats['hard'] ?? 0 }}</td>
            </tr>
        </table>
        <p class="text-xs text-gray-500 mt-3">Soal diambil acak dari bank yang berstatus <b>Publish</b>. Setiap soal bernilai 1 poin.</p>
    </div>
</div>

@endsection
