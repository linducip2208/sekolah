@extends('layouts.parent')
@section('title', 'Program Kerja OSIS')
@section('content')
@include('student-portal._nav')

<div class="mb-7">
    <div class="elite-kicker mb-2">Partisipasi Sekolah</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Program Kerja OSIS</h1>
    <div class="elite-rule"></div>
</div>

{{-- Propose form --}}
<div class="bg-white border border-rule p-5 mb-7">
    <div class="elite-kicker text-[.6rem] mb-3">Usulkan Program</div>
    <form method="POST" action="{{ route('student.osis.programs.propose') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold mb-1">Judul *</label>
            <input type="text" name="title" required maxlength="255" value="{{ old('title') }}" class="w-full border-2 border-rule px-3 py-2 text-sm">
            @error('title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-xs font-semibold mb-1">Deskripsi</label>
            <textarea name="description" rows="2" class="w-full border-2 border-rule px-3 py-2 text-sm">{{ old('description') }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-semibold mb-1">Estimasi anggaran (Rp)</label>
            <input type="number" name="budget" min="0" value="{{ old('budget') }}" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <button class="btn-elite text-xs">Kirim Usulan</button>
    </form>
</div>

{{-- List --}}
@forelse($programs as $p)
<div class="bg-white border border-rule p-5 mb-3">
    <div class="font-serif font-semibold ink-primary">{{ $p->title }}</div>
    <div class="text-xs text-gray-500 mt-1">Status: {{ ucfirst($p->status) }}@if($p->election) · {{ $p->election->title }}@endif</div>
    @if($p->description)<div class="mt-2 text-sm text-gray-600">{{ $p->description }}</div>@endif
</div>
@empty
<p class="font-serif text-gray-500 italic">Belum ada program kerja. Jadilah yang pertama mengusulkan!</p>
@endforelse
@endsection
