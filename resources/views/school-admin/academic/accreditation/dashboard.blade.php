@extends('layouts.school-admin')
@section('title', 'Dashboard Akreditasi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Accreditatio Scholae</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Dashboard Akreditasi (BAN-S/M)</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Tracker persiapan akreditasi berdasarkan standar IASP 2020.</p>
    <div class="flex gap-4 mt-3 text-sm">
        <a href="{{ route('admin.accreditation.instruments') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent">Instrumen</a>
        <a href="{{ route('admin.accreditation.documents') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent">Dokumen</a>
        <a href="{{ route('admin.accreditation.action-plans') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent">Rencana Perbaikan</a>
    </div>
</div>

{{-- Predicted Score & Grade --}}
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="elite-card p-5 text-center">
        <div class="elite-kicker text-[.55rem] mb-1">Prediksi Nilai</div>
        <div class="font-display text-3xl ink-primary">{{ number_format($predictedScore, 1) }}</div>
        <div class="text-xs text-gray-500 mt-1">dari 100</div>
    </div>
    <div class="elite-card p-5 text-center">
        <div class="elite-kicker text-[.55rem] mb-1">Predikat</div>
        <div class="font-display text-3xl" style="color:{{ $gradePrediction['color'] }}">{{ $gradePrediction['grade'] }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ $gradePrediction['label'] }}</div>
    </div>
    <div class="elite-card p-5 text-center">
        <div class="elite-kicker text-[.55rem] mb-1">Total Instrumen</div>
        <div class="font-display text-3xl ink-primary">{{ collect($standardProgress)->sum('total') }}</div>
        <div class="text-xs text-gray-500 mt-1">butir penilaian</div>
    </div>
    <div class="elite-card p-5 text-center">
        <div class="elite-kicker text-[.55rem] mb-1">Telah Dinilai</div>
        <div class="font-display text-3xl ink-primary">{{ collect($standardProgress)->sum('scored') }}</div>
        <div class="text-xs text-gray-500 mt-1">instrumen</div>
    </div>
</div>

{{-- Per Standard Progress --}}
<div class="grid lg:grid-cols-2 gap-6 mb-6">
    @foreach($standardProgress as $sp)
    @php $pct = $sp['total'] > 0 ? round(($sp['scored'] / $sp['total']) * 100) : 0; @endphp
    <div class="elite-card p-5">
        <div class="flex items-start justify-between mb-3">
            <div>
                <div class="elite-kicker text-[.55rem] mb-1">Standar {{ $sp['standard']->code }}</div>
                <h3 class="elite-h3 text-base ink-primary">{{ $sp['standard']->name }}</h3>
                <div class="text-xs text-gray-500 mt-1">Bobot: {{ $sp['standard']->weight_percent }}% | Rata-rata Nilai: {{ $sp['avg_score'] }}</div>
            </div>
            <div class="font-display text-2xl font-bold" style="color:{{ $pct >= 80 ? '#16A34A' : ($pct >= 50 ? '#b8860b' : '#DC2626') }}">{{ $pct }}%</div>
        </div>
        <div class="bg-gray-200 h-3 mb-3">
            <div class="h-3 transition-all duration-500" style="width:{{ $pct }}%; background:{{ $pct >= 80 ? '#16A34A' : ($pct >= 50 ? '#b8860b' : '#DC2626') }}"></div>
        </div>
        <div class="flex gap-4 text-xs text-gray-500">
            <span>{{ $sp['scored'] }}/{{ $sp['total'] }} dinilai</span>
            <span>{{ $sp['documents'] }} dokumen</span>
            <span>{{ $sp['approved_docs'] }} disetujui</span>
        </div>
    </div>
    @endforeach
</div>

{{-- Recent Documents --}}
<div class="elite-card overflow-hidden">
    <div class="px-5 py-4 border-b border-rule flex justify-between items-center">
        <h3 class="elite-h3 text-base ink-primary">Dokumen Terbaru</h3>
        <a href="{{ route('admin.accreditation.documents') }}" class="text-xs text-[var(--c-accent)] hover:underline">Lihat Semua</a>
    </div>
    <div class="table-scroll">
        <table class="w-full text-sm">
            <thead class="bg-[var(--c-primary)] text-white">
                <tr>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Instrumen</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Standar</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Deskripsi</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Diunggah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentDocs as $doc)
                <tr class="border-t border-rule">
                    <td class="px-4 py-3 font-mono text-xs">{{ $doc->instrument->number ?? '-' }}</td>
                    <td class="px-4 py-3 text-xs">{{ $doc->instrument->standard->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-xs">{{ Str::limit($doc->description, 50) }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs {{ $doc->status === 'approved' ? 'text-green-700' : ($doc->status === 'rejected' ? 'text-red-700' : 'text-amber-600') }}">
                            {{ $doc->status === 'approved' ? 'Disetujui' : ($doc->status === 'rejected' ? 'Ditolak' : 'Pending') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $doc->created_at->translatedFormat('d M Y') }}</td>
                </tr>
                @empty
                    <tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada dokumen diunggah.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6 flex gap-3">
    <a href="{{ route('admin.accreditation.instruments') }}" class="btn-elite" style="font-size:.65rem;">Instrumen & Penilaian</a>
    <a href="{{ route('admin.accreditation.documents') }}" class="btn-elite-ghost" style="font-size:.65rem;">Kelola Dokumen</a>
    <a href="{{ route('admin.accreditation.print-summary') }}" target="_blank" class="btn-elite-gold" style="font-size:.65rem;">Cetak Ringkasan</a>
</div>

@endsection
