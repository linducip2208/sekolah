@extends('layouts.school-admin')
@section('title', 'Hasil OCR')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.ai.ocr.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← OCR Dokumen</a>

<div class="mb-7">
    <div class="elite-kicker mb-2">OCR</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">{{ $result->filename }}</h1>
    <div class="elite-rule"></div>
</div>

<div class="bg-white border border-rule overflow-hidden">
    <div class="px-4 py-3 bg-gray-50 border-b border-rule elite-kicker text-[.7rem]">Teks Hasil Ekstraksi</div>
    <pre class="p-6 text-sm font-serif whitespace-pre-wrap">{{ $result->extracted_text }}</pre>
</div>

@endsection
