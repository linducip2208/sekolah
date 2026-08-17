@extends('layouts.school-admin')
@section('title', 'Sertifikat Kursus')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.courses.show', $certificate->enrollment->course_id) }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block no-print">← Kembali ke kursus</a>

<div class="max-w-3xl mx-auto">
    <div class="bg-white border-2 border-amber-400 p-10 text-center relative overflow-hidden">
        <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle at 20% 20%, #000 1px, transparent 1px), radial-gradient(circle at 80% 80%, #000 1px, transparent 1px); background-size: 40px 40px;"></div>
        <div class="relative">
            <div class="text-amber-600 text-5xl mb-4">🏅</div>
            <div class="elite-kicker text-[.7rem] text-gray-500 mb-2">SERTIFIKAT PENYELESAIAN</div>
            <div class="font-script italic text-2xl text-gray-400 mb-1">Diberikan kepada</div>
            <div class="font-display text-3xl ink-primary my-3">{{ $certificate->enrollment->student?->user?->name }}</div>
            <div class="text-gray-500 text-sm">atas keberhasilan menyelesaikan kursus</div>
            <div class="font-serif font-semibold text-xl ink-primary my-2">{{ $certificate->enrollment->course?->title }}</div>
            <div class="text-xs text-gray-400">dengan predikat Tuntas (progres 100%)</div>

            <div class="mt-8 flex justify-between items-end">
                <div class="text-left">
                    <div class="text-xs text-gray-500">No. Sertifikat</div>
                    <div class="font-mono text-sm ink-secondary">{{ $certificate->certificate_no }}</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 mb-1">Diterbitkan</div>
                    <div class="font-serif text-sm">{{ $certificate->issued_at?->format('d F Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 no-print text-center">
        <button onclick="window.print()" class="btn-elite">Cetak Sertifikat</button>
    </div>
</div>

@endsection
