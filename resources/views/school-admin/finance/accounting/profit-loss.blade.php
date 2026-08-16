@extends('layouts.school-admin')
@section('title', 'Laba Rugi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Ratio</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Laporan Laba Rugi</h1>
    <div class="elite-rule"></div>
</div>

<div class="flex gap-4 mb-4 text-sm">
    <a href="{{ route('admin.accounting.trial-balance') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent">Neraca Saldo</a>
    <a href="{{ route('admin.accounting.balance-sheet') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent">Neraca</a>
</div>

<form method="GET" class="bg-white border border-rule p-4 mb-4 flex gap-2 items-end">
    <div><label class="elite-kicker text-[.6rem] block">Dari</label><input type="date" name="from" value="{{ $from }}" class="border-2 border-rule px-2 py-1.5 font-mono text-xs"></div>
    <div><label class="elite-kicker text-[.6rem] block">Sampai</label><input type="date" name="to" value="{{ $to }}" class="border-2 border-rule px-2 py-1.5 font-mono text-xs"></div>
    <button class="btn-elite" style="padding:.5rem 1rem;font-size:.65rem;">Filter</button>
</form>

<div class="grid md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white border border-rule p-4"><div class="elite-kicker text-[.55rem] text-gray-500">Pendapatan</div><div class="font-display text-2xl text-green-700 mt-1">{{ number_format($pl['revenue']/100, 0, ',', '.') }}</div></div>
    <div class="bg-white border border-rule p-4"><div class="elite-kicker text-[.55rem] text-gray-500">Beban</div><div class="font-display text-2xl text-red-700 mt-1">{{ number_format($pl['expense']/100, 0, ',', '.') }}</div></div>
    <div class="bg-white border border-rule p-4"><div class="elite-kicker text-[.55rem] text-gray-500">Laba/Rugi Bersih</div><div class="font-display text-2xl {{ $pl['net_income'] >= 0 ? 'text-green-700' : 'text-red-700' }} mt-1">{{ number_format($pl['net_income']/100, 0, ',', '.') }}</div></div>
</div>

<div class="grid md:grid-cols-2 gap-6">
    <div class="bg-white border border-rule overflow-hidden">
        <div class="px-4 py-3 elite-kicker text-[.7rem] bg-gray-50 border-b border-rule">Pendapatan</div>
        <table class="w-full text-sm">
            @foreach($pl['revenue_accounts'] as $r)<tr class="border-b border-rule"><td class="px-4 py-2 font-serif">{{ $r->name }}</td><td class="px-4 py-2 text-right font-mono text-xs">{{ number_format($r->balance/100, 0, ',', '.') }}</td></tr>@endforeach
            @if($pl['revenue_accounts']->isEmpty())<tr><td class="p-4 text-center text-gray-400 italic">—</td></tr>@endif
        </table>
    </div>
    <div class="bg-white border border-rule overflow-hidden">
        <div class="px-4 py-3 elite-kicker text-[.7rem] bg-gray-50 border-b border-rule">Beban</div>
        <table class="w-full text-sm">
            @foreach($pl['expense_accounts'] as $r)<tr class="border-b border-rule"><td class="px-4 py-2 font-serif">{{ $r->name }}</td><td class="px-4 py-2 text-right font-mono text-xs">{{ number_format($r->balance/100, 0, ',', '.') }}</td></tr>@endforeach
            @if($pl['expense_accounts']->isEmpty())<tr><td class="p-4 text-center text-gray-400 italic">—</td></tr>@endif
        </table>
    </div>
</div>

@endsection
