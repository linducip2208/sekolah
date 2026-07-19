@extends('layouts.school-admin')
@section('title', 'Bukti Adiwiyata — ' . $indicator->code)
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@section('content')
<div class="mb-6">
    <div class="elite-kicker mb-2">Adiwiyata · {{ $indicator->category?->name }}</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Indikator {{ $indicator->code }}</h1>
    <div class="elite-rule"></div>
</div>

<div class="elite-card p-6 mb-6">
    <div class="font-mono text-xs mb-2">{{ $indicator->code }} · Max Skor: {{ $indicator->max_score }} · Tipe: {{ $indicator->evidence_type }}</div>
    <p class="font-serif text-lg mb-3">{{ $indicator->description }}</p>
    @if($indicator->evidence_hint)
    <p class="text-xs text-gray-500 italic">💡 {{ $indicator->evidence_hint }}</p>
    @endif
</div>

<div class="elite-card p-6 mb-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">{{ $existing ? 'Edit Bukti' : 'Upload Bukti Baru' }}</h3>
    <form method="POST" action="{{ route('admin.adiwiyata.evidence.store', $indicator) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Judul Bukti *</label>
            <input type="text" name="title" required value="{{ $existing?->title }}" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Deskripsi</label>
            <textarea name="description" rows="3" class="w-full border-2 border-rule px-3 py-2 text-sm">{{ $existing?->description }}</textarea>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Skor Self-Assessment (0-{{ $indicator->max_score }}) *</label>
            <input type="number" name="score" required min="0" max="{{ $indicator->max_score }}" value="{{ $existing?->score ?? 0 }}" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Upload File Bukti (Multiple)</label>
            <input type="file" name="files[]" multiple class="w-full border-2 border-rule px-3 py-2 text-sm">
            @if($existing && $existing->file_path)
            <div class="text-xs text-gray-500 mt-1">{{ count($existing->file_path) }} file sudah diupload</div>
            @endif
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Catatan</label>
            <input type="text" name="notes" value="{{ $existing?->notes }}" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <button type="submit" class="btn-elite-gold">Simpan Bukti</button>
    </form>
</div>

@if($allEvidences->count() > 0)
<div class="elite-card overflow-hidden">
    <h3 class="elite-h3 text-lg ink-primary p-4 mb-0">Semua Bukti Terupload</h3>
    <div class="table-scroll">
    <table class="table-elite w-full text-sm">
        <thead>
            <tr>
                <th>Indikator</th>
                <th>Judul</th>
                <th>Skor</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($allEvidences as $ev)
            <tr>
                <td class="font-mono text-[.6rem]">{{ $ev->indicator?->code }}</td>
                <td class="text-xs">{{ $ev->title }}</td>
                <td class="font-mono text-xs">{{ $ev->score }}/{{ $ev->indicator?->max_score }}</td>
                <td><span class="text-[.6rem] uppercase px-2 py-0.5 rounded
                    {{ $ev->status === 'verified' ? 'bg-green-100 text-green-800' : ($ev->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                    {{ $ev->status }}</span></td>
                <td class="text-xs">{{ $ev->created_at->format('d/m/Y') }}</td>
                <td class="text-right whitespace-nowrap">
                    @if($ev->status === 'submitted')
                    <form method="POST" action="{{ route('admin.adiwiyata.evidence.verify', $ev) }}" class="inline mr-1">
                        @csrf <button class="text-xs underline text-green-600">Verifikasi</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('admin.adiwiyata.evidence.reject', $ev) }}" class="inline mr-1">
                        @csrf <button class="text-xs underline text-yellow-600">Tolak</button>
                    </form>
                    <form method="POST" action="{{ route('admin.adiwiyata.evidence.delete', $ev) }}" class="inline" onsubmit="return confirm('Hapus bukti?')">
                        @csrf @method('DELETE')
                        <button class="text-xs underline text-red-600">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
<div class="mt-4">{{ $allEvidences->links() }}</div>
@endif
@endsection
