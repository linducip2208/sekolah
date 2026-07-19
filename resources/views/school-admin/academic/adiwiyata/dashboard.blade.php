@extends('layouts.school-admin')
@section('title', 'Adiwiyata — Sekolah Hijau')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@section('content')
<div class="mb-6">
    <div class="elite-kicker mb-2">Akademik</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Adiwiyata Tracker</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Sekolah Hijau — PermenLHK No. 53/2019</p>
</div>

<div class="grid md:grid-cols-4 gap-4 mb-6">
    <div class="elite-card p-5 text-center col-span-1">
        <div class="elite-kicker text-[.55rem] mb-2">Total Skor</div>
        <div class="font-display text-4xl ink-accent">{{ $totalScore }}</div>
        <div class="text-xs text-gray-500 mt-1">dari {{ $maxScore }} maksimal</div>
    </div>
    <div class="elite-card p-5 text-center col-span-1">
        <div class="elite-kicker text-[.55rem] mb-2">Persentase</div>
        <div class="font-display text-4xl ink-primary">{{ $overallPercentage }}%</div>
    </div>
    <div class="elite-card p-5 text-center col-span-1">
        <div class="elite-kicker text-[.55rem] mb-2">Prediksi Level</div>
        <div class="font-display text-4xl ink-primary">{{ $predictedLevel }}</div>
    </div>
    <div class="elite-card p-5 text-center col-span-1">
        <div class="elite-kicker text-[.55rem] mb-2">Pencapaian</div>
        <div class="text-xs">{{ $levels->count() }} level tercatat</div>
        @if($levels->first())
        <div class="text-[.55rem] text-gray-500 mt-1">Terbaru: {{ $levels->first()->achieved_level }} ({{ $levels->first()->achieved_date->format('d/m/Y') }})</div>
        @endif
    </div>
</div>

@if($levels->isNotEmpty())
<div class="elite-card p-5 mb-6">
    <h3 class="elite-h3 text-base ink-primary mb-3">Riwayat Pencapaian Level</h3>
    <div class="space-y-2">
        @foreach($levels as $level)
        <div class="flex items-center justify-between p-2 border border-rule">
            <span class="text-xs uppercase font-semibold">{{ $level->achieved_level }}</span>
            <span class="text-xs">{{ $level->achieved_date->format('d F Y') }}</span>
            @if($level->certificate_number)
            <span class="text-[.55rem] font-mono">{{ $level->certificate_number }}</span>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="mb-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Progress per Kategori</h3>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($progress as $p)
        <a href="{{ route('admin.adiwiyata.indicators', ['category' => $p['category']->id]) }}" class="elite-card p-5 group block">
            <div class="elite-kicker text-[.55rem] mb-2">Aspek {{ $loop->iteration }}</div>
            <h3 class="elite-h3 text-base ink-primary mb-2">{{ $p['category']->name }}</h3>
            <div class="text-xs text-gray-500 mb-3">{{ $p['completedIndicators'] }}/{{ $p['totalIndicators'] }} indikator terpenuhi</div>
            <div class="bg-gray-100 h-2 mb-2">
                <div class="h-full bg-[var(--c-accent)]" style="width:{{ $p['percentage'] }}%"></div>
            </div>
            <div class="flex justify-between text-[.6rem]">
                <span>Skor: {{ $p['score'] }}/{{ $p['maxScore'] }}</span>
                <span class="font-mono">{{ $p['percentage'] }}%</span>
            </div>
        </a>
        @endforeach
    </div>
</div>

<div class="elite-card p-6 mb-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Catat Pencapaian Level</h3>
    <form method="POST" action="{{ route('admin.adiwiyata.levels.store') }}" enctype="multipart/form-data" class="grid md:grid-cols-2 gap-4">
        @csrf
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Level *</label>
            <select name="achieved_level" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                <option value="mandiri">Mandiri</option>
                <option value="madya">Madya</option>
                <option value="pratama">Pratama</option>
                <option value="calon">Calon</option>
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Tanggal Pencapaian *</label>
            <input type="date" name="achieved_date" required class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">No Sertifikat</label>
            <input type="text" name="certificate_number" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">File Sertifikat</label>
            <input type="file" name="certificate_file" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="block elite-kicker text-[.6rem] mb-1">Catatan</label>
            <textarea name="notes" rows="2" class="w-full border-2 border-rule px-3 py-2 text-sm"></textarea>
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="btn-elite">Catat Level</button>
        </div>
    </form>
</div>

<a href="{{ route('admin.adiwiyata.indicators') }}" class="btn-elite-gold">Lihat Semua Indikator</a>
@endsection
