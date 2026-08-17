@extends('layouts.school-admin')
@section('title', 'OCR Dokumen')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">OCR</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">OCR Dokumen</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">Unggah gambar (scan dokumen) untuk diekstrak teksnya menggunakan AI.</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<form method="POST" action="{{ route('admin.ai.ocr.upload') }}" enctype="multipart/form-data" class="bg-white border border-rule p-4 mb-6 flex gap-2 items-center">
    @csrf
    <input type="file" name="file" accept=".jpg,.jpeg,.png,.webp" required class="border-2 border-rule px-3 py-2 text-sm">
    <button class="btn-elite">Unggah & OCR</button>
</form>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white"><tr>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">File</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Waktu</th>
            <th class="px-4 py-3"></th>
        </tr></thead>
        <tbody>
            @forelse($results as $r)
            <tr class="border-t border-rule hover:bg-gray-50">
                <td class="px-4 py-3 font-serif text-xs">{{ $r->filename }}</td>
                <td class="px-4 py-3">
                    @if($r->status === 'completed')<span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-800">✓ Selesai</span>
                    @else<span class="text-xs px-2 py-0.5 rounded bg-red-100 text-red-800">Gagal</span>@endif
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ $r->created_at->diffForHumans() }}</td>
                <td class="px-4 py-3 text-right">
                    @if($r->status === 'completed')<a href="{{ route('admin.ai.ocr.show', $r) }}" class="text-xs underline ink-secondary">Lihat Teks</a>@endif
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada dokumen OCR.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $results->links() }}</div>

@endsection
