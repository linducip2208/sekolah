@extends('layouts.school-admin')
@section('title', 'BKK — Bursa Kerja Khusus')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@section('content')
<div class="mb-6">
    <div class="elite-kicker mb-2">Bursa Kerja Khusus SMK</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Dashboard BKK</h1>
    <div class="elite-rule"></div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="elite-card p-5 text-center">
        <div class="font-display text-3xl ink-primary">{{ $partnerStats['total'] }}</div>
        <div class="elite-kicker text-[.55rem] mt-1">Total Mitra</div>
        <div class="text-xs text-gray-500 mt-1">{{ $partnerStats['active_mou'] }} MoU Aktif</div>
    </div>
    <div class="elite-card p-5 text-center">
        <div class="font-display text-3xl ink-primary">{{ $totalPlacements }}</div>
        <div class="elite-kicker text-[.55rem] mt-1">Total Penempatan</div>
        <div class="text-xs text-gray-500 mt-1">{{ $activePlacements }} Aktif</div>
    </div>
    <div class="elite-card p-5 text-center">
        <div class="font-display text-3xl ink-accent">{{ $placementRate }}%</div>
        <div class="elite-kicker text-[.55rem] mt-1">Tingkat Penempatan</div>
    </div>
    <div class="elite-card p-5 text-center">
        <div class="font-display text-3xl" style="color:var(--c-muted);">{{ $partnerStats['expired'] }}</div>
        <div class="elite-kicker text-[.55rem] mt-1">MoU Kadaluarsa</div>
    </div>
</div>

@if($latestReport)
<div class="elite-card p-6 mb-8">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Laporan Terbaru — {{ $latestReport->report_date->format('d F Y') }}</h3>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        @foreach([['Lulusan', $latestReport->total_graduates], ['Ditempatkan', $latestReport->total_placed], ['Wirausaha', $latestReport->total_entrepreneur], ['Kuliah', $latestReport->total_university], ['Belum', $latestReport->total_unemployed]] as [$l, $v])
        <div class="text-center border border-rule p-3">
            <div class="font-display text-xl ink-primary">{{ $v }}</div>
            <div class="text-[.6rem] uppercase tracking-wider text-gray-500">{{ $l }}</div>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="grid lg:grid-cols-2 gap-6">
    <div class="elite-card p-6">
        <h3 class="elite-h3 text-base ink-primary mb-4">Industri Penempatan</h3>
        @if(count($industryBreakdown) > 0)
        <div class="space-y-2">
            @foreach($industryBreakdown as $industry => $count)
            <div class="flex items-center gap-3">
                <div class="text-xs w-28 truncate">{{ $industry }}</div>
                <div class="flex-1 bg-gray-100 h-4">
                    <div class="h-full bg-[var(--c-accent)]" style="width:{{ max($count / max(array_values($industryBreakdown)), 0.05) * 100 }}%"></div>
                </div>
                <div class="font-mono text-xs">{{ $count }}</div>
            </div>
            @endforeach
        </div>
        @else
        <p class="font-serif text-sm text-gray-500 italic">Belum ada data penempatan.</p>
        @endif
    </div>
    <div class="elite-card p-6">
        <h3 class="elite-h3 text-base ink-primary mb-4">Penempatan Terbaru</h3>
        @if($recentPlacements->count() > 0)
        <div class="space-y-3">
            @foreach($recentPlacements as $p)
            <div class="flex justify-between items-center pb-3 border-b border-rule last:border-0">
                <div>
                    <div class="font-serif font-semibold text-sm">{{ $p->student?->name }}</div>
                    <div class="text-xs text-gray-500">{{ $p->position }} · {{ $p->partner?->company_name }}</div>
                </div>
                <span class="text-xs px-2 py-1 rounded {{ $p->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($p->status) }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="font-serif text-sm text-gray-500 italic">Belum ada penempatan.</p>
        @endif
    </div>
</div>

<div class="mt-8 flex gap-3">
    <a href="{{ route('admin.bkk.partners') }}" class="btn-elite-gold">Mitra & MoU</a>
    <a href="{{ route('admin.bkk.placements') }}" class="btn-elite-ghost">Penempatan</a>
    <a href="{{ route('admin.bkk.reports') }}" class="btn-elite-ghost">Laporan</a>
</div>
@endsection
