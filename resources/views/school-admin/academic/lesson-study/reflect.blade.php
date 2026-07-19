@extends('layouts.school-admin')
@section('title', 'Refleksi — ' . $lessonStudy->title)
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="flex justify-between items-end">
        <div>
            <div class="elite-kicker mb-2">Phase: See</div>
            <h1 class="elite-h1 text-2xl ink-primary mb-2">Refleksi: {{ $lessonStudy->title }}</h1>
            <div class="elite-rule"></div>
            <p class="font-serif text-sm text-gray-600 mt-2">Fase refleksi — bagikan temuan, kekuatan, area perbaikan, dan rencana tindak lanjut.</p>
        </div>
        <a href="{{ route('admin.lesson-study.index') }}" class="btn-elite-ghost">← Kembali</a>
    </div>
</div>

<div class="max-w-2xl">
    <div class="bg-white border border-rule p-6">
        <h3 class="elite-h3 text-base ink-primary mb-4">{{ $existingReflection ? 'Perbarui' : 'Tulis' }} Refleksi Anda</h3>
        <form method="POST" action="{{ route('admin.lesson-study.store-reflection', $lessonStudy) }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Refleksi Pembelajaran <span class="text-red-500">*</span></label>
                    <textarea name="reflection_text" required rows="5" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Deskripsikan hasil observasi Anda. Apa yang berjalan baik? Apa yang bisa diperbaiki?">{{ old('reflection_text', $existingReflection->reflection_text ?? '') }}</textarea>
                </div>

                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Kekuatan / Hal Positif</label>
                    <textarea name="strength_points" rows="3" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Apa saja kekuatan dari pembelajaran yang baru diamati?">{{ old('strength_points', $existingReflection->strength_points ?? '') }}</textarea>
                </div>

                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Area Perbaikan</label>
                    <textarea name="improvement_points" rows="3" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Apa yang bisa ditingkatkan?">{{ old('improvement_points', $existingReflection->improvement_points ?? '') }}</textarea>
                </div>

                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Rencana Tindak Lanjut</label>
                    <textarea name="action_plan" rows="3" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Apa yang akan Anda lakukan setelah refleksi ini?">{{ old('action_plan', $existingReflection->action_plan ?? '') }}</textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('admin.lesson-study.index') }}" class="btn-elite-ghost">Batal</a>
                    <button class="btn-elite">Simpan Refleksi</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="mt-8 max-w-2xl">
    <div class="bg-amber-50 border border-amber-200 p-5">
        <h3 class="elite-h3 text-sm ink-primary mb-2">Anggota Tim</h3>
        <ul class="space-y-1">
            @foreach($lessonStudy->members as $m)
                <li class="text-sm font-serif">{{ $m->staff->name ?? '—' }} <span class="text-xs text-gray-500">({{ $m->role }})</span></li>
            @endforeach
        </ul>
    </div>
</div>

@endsection
