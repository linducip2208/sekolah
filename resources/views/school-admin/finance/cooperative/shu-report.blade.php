@extends('layouts.school-admin')
@section('title', 'Laporan SHU Koperasi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@section('content')
<div class="mb-6">
    <div class="elite-kicker mb-2">Koperasi</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Laporan SHU {{ $tahun }}</h1>
    <div class="elite-rule"></div>
</div>

<form method="GET" class="flex gap-3 mb-6 bg-white border border-rule p-4 items-end">
    <div>
        <label class="block elite-kicker text-[.6rem] mb-1">Tahun</label>
        <input type="number" name="tahun" value="{{ $tahun }}" min="2020" max="2099" class="border-2 border-rule px-3 py-2 text-sm">
    </div>
    <button type="submit" class="btn-elite text-xs">Lihat</button>
    <a href="{{ route('admin.cooperative.shu-report', ['tahun' => $tahun]) }}" target="_blank" class="btn-elite-ghost text-xs">Cetak PDF</a>
</form>

<div class="elite-card p-8 max-w-2xl mx-auto" id="shu-print">
    <div class="ornament-center mb-2"></div>
    <h2 class="elite-h2 text-2xl ink-primary text-center mb-1">Laporan Sisa Hasil Usaha (SHU)</h2>
    <div class="text-center elite-kicker text-[.55rem] mb-6">Koperasi Sekolah — Tahun Buku {{ $tahun }}</div>
    <div class="elite-rule mb-6" style="text-align:center;display:block;"></div>

    <div class="space-y-4 text-sm">
        <div class="flex justify-between py-2 border-b border-rule">
            <span class="font-serif">Total Pinjaman Disalurkan</span>
            <span class="font-mono">Rp {{ number_format($shu['total_loans'] / 100, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between py-2 border-b border-rule">
            <span class="font-serif">Pendapatan Bunga</span>
            <span class="font-mono">Rp {{ number_format($shu['total_interest'] / 100, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between py-2 border-b border-rule">
            <span class="font-serif font-semibold">Surplus Kotor (SHU)</span>
            <span class="font-mono font-semibold ink-accent">Rp {{ number_format($shu['gross_surplus'] / 100, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between py-2 border-b border-rule pl-4">
            <span class="font-serif text-xs">Dana Cadangan (25%)</span>
            <span class="font-mono text-xs">Rp {{ number_format($shu['reserve'] / 100, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between py-2 border-b border-rule pl-4">
            <span class="font-serif text-xs">Bagian Anggota (75%)</span>
            <span class="font-mono text-xs">Rp {{ number_format($shu['member_share'] / 100, 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between py-3 font-semibold">
            <span>Jumlah Anggota Aktif</span>
            <span class="font-display text-xl">{{ $shu['member_count'] }} orang</span>
        </div>
        @if($shu['member_count'] > 0)
        <div class="flex justify-between py-2 border-t border-rule">
            <span class="font-serif"><em>Estimasi SHU per Anggota</em></span>
            <span class="font-mono">Rp {{ number_format(($shu['member_share'] / $shu['member_count']) / 100, 0, ',', '.') }}</span>
        </div>
        @endif
    </div>

    <div class="text-center mt-8 text-xs text-gray-500 font-serif italic">
        Dicetak secara digital oleh sistem Sikad Pro · {{ now()->translatedFormat('d F Y') }}
    </div>
</div>
@endsection
