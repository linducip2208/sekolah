@extends('layouts.school-admin')
@section('title', 'Koperasi Sekolah')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@section('content')
<div class="mb-6">
    <div class="elite-kicker mb-2">Keuangan SPP</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Koperasi Sekolah</h1>
    <div class="elite-rule"></div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="elite-card p-5 text-center">
        <div class="font-display text-3xl ink-primary">{{ $totalMembers }}</div>
        <div class="elite-kicker text-[.55rem] mt-1">Anggota</div>
        <div class="text-xs text-gray-500 mt-1">{{ $activeMembers }} Aktif</div>
    </div>
    <div class="elite-card p-5 text-center">
        <div class="font-display text-3xl ink-primary">Rp {{ number_format($totalSavings / 100, 0, ',', '.') }}</div>
        <div class="elite-kicker text-[.55rem] mt-1">Total Simpanan</div>
    </div>
    <div class="elite-card p-5 text-center">
        <div class="font-display text-3xl ink-accent">Rp {{ number_format($outstanding / 100, 0, ',', '.') }}</div>
        <div class="elite-kicker text-[.55rem] mt-1">Pinjaman Beredar</div>
    </div>
    <div class="elite-card p-5 text-center">
        <div class="font-display text-3xl" style="color:var(--c-muted);">{{ $shu['member_count'] }}</div>
        <div class="elite-kicker text-[.55rem] mt-1">Penerima SHU</div>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6 mb-8">
    <div class="elite-card p-6">
        <h3 class="elite-h3 text-base ink-primary mb-4">Proyeksi SHU {{ date('Y') }}</h3>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between"><span>Total Pinjaman</span><span class="font-mono">Rp {{ number_format($shu['total_loans'] / 100, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span>Pendapatan Bunga</span><span class="font-mono">Rp {{ number_format($shu['total_interest'] / 100, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span>Surplus Kotor</span><span class="font-mono ink-accent font-semibold">Rp {{ number_format($shu['gross_surplus'] / 100, 0, ',', '.') }}</span></div>
            <div class="elite-rule w-full my-2"></div>
            <div class="flex justify-between"><span>Cadangan (25%)</span><span class="font-mono">Rp {{ number_format($shu['reserve'] / 100, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span>Bagian Anggota (75%)</span><span class="font-mono">Rp {{ number_format($shu['member_share'] / 100, 0, ',', '.') }}</span></div>
        </div>
    </div>
    <div class="elite-card p-6">
        <h3 class="elite-h3 text-base ink-primary mb-4">Status Pinjaman</h3>
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <div class="flex-1 text-xs">Aktif</div>
                <div class="font-mono text-xl ink-primary">{{ $activeLoans }}</div>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex-1 text-xs">Menunggu Persetujuan</div>
                <div class="font-mono text-xl text-yellow-600">{{ $pendingLoans }}</div>
            </div>
        </div>
    </div>
</div>

<div class="flex gap-3 mb-6">
    <a href="{{ route('admin.cooperative.members') }}" class="btn-elite-gold">Anggota</a>
    <a href="{{ route('admin.cooperative.savings') }}" class="btn-elite-ghost">Simpanan</a>
    <a href="{{ route('admin.cooperative.loans') }}" class="btn-elite-ghost">Pinjaman</a>
    <a href="{{ route('admin.cooperative.shu-report') }}" class="btn-elite-ghost">Laporan SHU</a>
</div>

@if($recentSavings->count() > 0)
<div class="elite-card p-6 mb-6">
    <h3 class="elite-h3 text-base ink-primary mb-4">Simpanan Terbaru</h3>
    <div class="space-y-2">
        @foreach($recentSavings as $s)
        <div class="flex justify-between items-center p-3 border border-rule">
            <div>
                <div class="font-serif font-semibold text-sm">{{ $s->member?->member_number }}</div>
                <div class="text-xs text-gray-500">{{ $s->savings_type }} · {{ $s->transaction_date->format('d/m/Y') }}</div>
            </div>
            <span class="font-mono text-sm {{ $s->transaction_type === 'deposit' ? 'text-green-700' : 'text-red-700' }}">
                {{ $s->transaction_type === 'deposit' ? '+' : '-' }}Rp {{ number_format($s->amount / 100, 0, ',', '.') }}
            </span>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
