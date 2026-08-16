@extends('layouts.school-admin')
@section('title', 'Neraca Saldo')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Statera</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Neraca Saldo</h1>
    <div class="elite-rule"></div>
</div>

<div class="flex gap-4 mb-4 text-sm">
    <a href="{{ route('admin.accounting.profit-loss') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent">Laba Rugi</a>
    <a href="{{ route('admin.accounting.balance-sheet') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent">Neraca</a>
</div>

<form method="GET" class="bg-white border border-rule p-4 mb-4 flex gap-2 items-end">
    <div><label class="elite-kicker text-[.6rem] block">Dari</label><input type="date" name="from" value="{{ $from }}" class="border-2 border-rule px-2 py-1.5 font-mono text-xs"></div>
    <div><label class="elite-kicker text-[.6rem] block">Sampai</label><input type="date" name="to" value="{{ $to }}" class="border-2 border-rule px-2 py-1.5 font-mono text-xs"></div>
    <button class="btn-elite" style="padding:.5rem 1rem;font-size:.65rem;">Filter</button>
</form>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white"><tr>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kode</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Akun</th>
            <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Debit</th>
            <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Kredit</th>
            <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Saldo</th>
        </tr></thead>
        <tbody>
            @php $totalDebit = 0; $totalCredit = 0; @endphp
            @forelse($rows as $r)
                @php
                    $totalDebit += $r->debit;
                    $totalCredit += $r->credit;
                    $balanceSide = ($r->normal_balance === 'debit' && $r->balance >= 0) || ($r->normal_balance === 'credit' && $r->balance < 0);
                @endphp
                <tr class="border-t border-rule">
                    <td class="px-4 py-2 font-mono text-xs">{{ $r->code }}</td>
                    <td class="px-4 py-2 font-serif">{{ $r->name }}</td>
                    <td class="px-4 py-2 text-right font-mono text-xs">{{ $r->debit ? number_format($r->debit/100, 0, ',', '.') : '' }}</td>
                    <td class="px-4 py-2 text-right font-mono text-xs">{{ $r->credit ? number_format($r->credit/100, 0, ',', '.') : '' }}</td>
                    <td class="px-4 py-2 text-right font-mono text-xs">{{ $r->balance ? number_format(abs($r->balance)/100, 0, ',', '.') : '0' }} {{ $r->balance >= 0 ? 'D' : 'K' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada transaksi yang diposting.</td></tr>
            @endforelse
            <tr class="border-t-2 border-rule bg-gray-50 font-semibold">
                <td class="px-4 py-2" colspan="2">Total</td>
                <td class="px-4 py-2 text-right font-mono">{{ number_format($totalDebit/100, 0, ',', '.') }}</td>
                <td class="px-4 py-2 text-right font-mono">{{ number_format($totalCredit/100, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>

@endsection
