@extends('layouts.school-admin')
@section('title', 'Neraca')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Bilanx</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Neraca (Balance Sheet)</h1>
    <div class="elite-rule"></div>
</div>

<div class="flex gap-4 mb-4 text-sm">
    <a href="{{ route('admin.accounting.trial-balance') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent">Neraca Saldo</a>
    <a href="{{ route('admin.accounting.profit-loss') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent">Laba Rugi</a>
</div>

<form method="GET" class="bg-white border border-rule p-4 mb-4 flex gap-2 items-end">
    <div><label class="elite-kicker text-[.6rem] block">Per Tanggal</label><input type="date" name="as_of" value="{{ $asOf }}" class="border-2 border-rule px-2 py-1.5 font-mono text-xs"></div>
    <button class="btn-elite" style="padding:.5rem 1rem;font-size:.65rem;">Filter</button>
</form>

<div class="grid md:grid-cols-2 gap-6">
    <div>
        <div class="bg-white border border-rule overflow-hidden mb-6">
            <div class="px-4 py-3 elite-kicker text-[.7rem] bg-gray-50 border-b border-rule">Aset</div>
            <table class="w-full text-sm">
                @foreach($bs['asset_accounts'] as $r)<tr class="border-b border-rule"><td class="px-4 py-2 font-serif">{{ $r->name }}</td><td class="px-4 py-2 text-right font-mono text-xs">{{ number_format($r->balance/100, 0, ',', '.') }}</td></tr>@endforeach
                <tr class="bg-gray-50 font-semibold"><td class="px-4 py-2">Total Aset</td><td class="px-4 py-2 text-right font-mono">{{ number_format($bs['assets']/100, 0, ',', '.') }}</td></tr>
            </table>
        </div>
    </div>
    <div>
        <div class="bg-white border border-rule overflow-hidden mb-6">
            <div class="px-4 py-3 elite-kicker text-[.7rem] bg-gray-50 border-b border-rule">Kewajiban</div>
            <table class="w-full text-sm">
                @foreach($bs['liability_accounts'] as $r)<tr class="border-b border-rule"><td class="px-4 py-2 font-serif">{{ $r->name }}</td><td class="px-4 py-2 text-right font-mono text-xs">{{ number_format($r->balance/100, 0, ',', '.') }}</td></tr>@endforeach
                <tr class="bg-gray-50 font-semibold"><td class="px-4 py-2">Total Kewajiban</td><td class="px-4 py-2 text-right font-mono">{{ number_format($bs['liabilities']/100, 0, ',', '.') }}</td></tr>
            </table>
        </div>
        <div class="bg-white border border-rule overflow-hidden">
            <div class="px-4 py-3 elite-kicker text-[.7rem] bg-gray-50 border-b border-rule">Ekuitas</div>
            <table class="w-full text-sm">
                @foreach($bs['equity_accounts'] as $r)<tr class="border-b border-rule"><td class="px-4 py-2 font-serif">{{ $r->name }}</td><td class="px-4 py-2 text-right font-mono text-xs">{{ number_format($r->balance/100, 0, ',', '.') }}</td></tr>@endforeach
                <tr class="border-b border-rule"><td class="px-4 py-2">Laba/Rugi Berjalan</td><td class="px-4 py-2 text-right font-mono text-xs">{{ number_format($bs['net_income']/100, 0, ',', '.') }}</td></tr>
                <tr class="bg-gray-50 font-semibold"><td class="px-4 py-2">Total Ekuitas</td><td class="px-4 py-2 text-right font-mono">{{ number_format($bs['total_equity']/100, 0, ',', '.') }}</td></tr>
            </table>
        </div>
    </div>
</div>

<div class="bg-white border border-rule p-4 mt-2">
    <div class="elite-kicker text-[.7rem] mb-1">Total Kewajiban + Ekuitas</div>
    <div class="font-display text-2xl ink-primary">{{ number_format($bs['liabilities_plus_equity']/100, 0, ',', '.') }}</div>
    <div class="text-xs mt-1 {{ $bs['assets'] === $bs['liabilities_plus_equity'] ? 'text-green-700' : 'text-red-700' }}">
        {{ $bs['assets'] === $bs['liabilities_plus_equity'] ? '✓ Neraca seimbang' : '✗ Neraca belum seimbang (selisih ' . number_format(($bs['assets'] - $bs['liabilities_plus_equity'])/100, 0, ',', '.') . ')' }}
    </div>
</div>

@endsection
