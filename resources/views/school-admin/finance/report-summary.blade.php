@extends('layouts.school-admin')
@section('title', 'Laporan Keuangan')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7"><div class="elite-kicker mb-2">Tabulationes Financiariae · Sekolah</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Laporan Keuangan Sekolah</h1>
<div class="elite-rule"></div>
<p class="font-serif text-base text-gray-600 mt-3">Pemasukan SPP/donasi/event dan pengeluaran gaji/maintenance.</p></div>

<form method="GET" class="bg-white border border-rule p-5 mb-6 grid grid-cols-1 md:grid-cols-5 gap-3">
<div><label class="elite-kicker text-[.6rem] block mb-1">Dari</label>
<input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Sampai</label>
<input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
<div class="flex items-end gap-2 md:col-span-3">
<button class="btn-elite" style="padding:.6rem 1rem;font-size:.65rem;">Filter</button>
<a href="{{ route('admin.finance.reports.outstanding') }}" class="btn-elite-ghost" style="padding:.6rem 1rem;font-size:.65rem;">Tunggakan SPP →</a>
<a href="{{ route('admin.finance.reports.export', request()->all()) }}" class="btn-elite-gold" style="padding:.6rem 1rem;font-size:.65rem;">⤓ Export CSV</a>
</div>
</form>

{{-- KPI Pemasukan --}}
<div class="mb-6">
<div class="elite-kicker mb-2" style="color: var(--c-accent);">PEMASUKAN</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
<div class="bg-white border-l-4 border-green-600 p-5">
<div class="elite-kicker text-[.6rem]">SPP / Tagihan Lunas</div>
<div class="font-display text-xl ink-primary mt-2">Rp {{ number_format($sppCollected/100, 0, ',', '.') }}</div>
</div>
<div class="bg-white border-l-4 border-blue-600 p-5">
<div class="elite-kicker text-[.6rem]">Donasi Diterima</div>
<div class="font-display text-xl ink-primary mt-2">Rp {{ number_format($donationsReceived/100, 0, ',', '.') }}</div>
</div>
<div class="bg-white border-l-4 border-purple-600 p-5">
<div class="elite-kicker text-[.6rem]">Pendapatan Event</div>
<div class="font-display text-xl ink-primary mt-2">Rp {{ number_format($eventRevenue/100, 0, ',', '.') }}</div>
</div>
<div class="bg-white border-l-4 border-emerald-700 p-5" style="background:rgba(16,185,129,.08);">
<div class="elite-kicker text-[.6rem]" style="color:#047857;">TOTAL PEMASUKAN</div>
<div class="font-display text-2xl ink-primary mt-2">Rp {{ number_format($totalIncome/100, 0, ',', '.') }}</div>
</div>
</div>
</div>

{{-- KPI Pengeluaran --}}
<div class="mb-6">
<div class="elite-kicker mb-2" style="color: var(--c-accent);">PENGELUARAN</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
<div class="bg-white border-l-4 border-orange-600 p-5">
<div class="elite-kicker text-[.6rem]">Gaji Staff (Paid)</div>
<div class="font-display text-xl ink-primary mt-2">Rp {{ number_format($payrollPaid/100, 0, ',', '.') }}</div>
</div>
<div class="bg-white border-l-4 border-yellow-600 p-5">
<div class="elite-kicker text-[.6rem]">Maintenance / Perbaikan</div>
<div class="font-display text-xl ink-primary mt-2">Rp {{ number_format($maintenanceCost/100, 0, ',', '.') }}</div>
</div>
<div class="bg-white border-l-4 border-red-700 p-5" style="background:rgba(239,68,68,.06);">
<div class="elite-kicker text-[.6rem]" style="color:#b91c1c;">TOTAL PENGELUARAN</div>
<div class="font-display text-2xl ink-primary mt-2">Rp {{ number_format($totalExpense/100, 0, ',', '.') }}</div>
</div>
<div class="bg-white border-l-4 {{ $netCash >= 0 ? 'border-green-700' : 'border-red-700' }} p-5"
     style="background: {{ $netCash >= 0 ? 'rgba(184,134,11,.10)' : 'rgba(239,68,68,.10)' }};">
<div class="elite-kicker text-[.6rem]" style="color: var(--c-accent);">NET CASH FLOW</div>
<div class="font-display text-2xl mt-2 {{ $netCash >= 0 ? 'text-green-700' : 'text-red-700' }}">Rp {{ number_format($netCash/100, 0, ',', '.') }}</div>
</div>
</div>
</div>

{{-- Outstanding alert --}}
<div class="bg-white border-l-4 border-yellow-700 p-5 mb-6" style="background:rgba(234,179,8,.06);">
<div class="flex justify-between items-center">
<div>
<div class="elite-kicker text-[.65rem] mb-1" style="color:#a16207;">⚠ TUNGGAKAN SPP</div>
<div class="font-display text-2xl ink-primary">Rp {{ number_format($outstanding/100, 0, ',', '.') }}</div>
<div class="text-xs text-gray-600 mt-1">{{ $outstandingCount }} invoice belum lunas / overdue</div>
</div>
<a href="{{ route('admin.finance.reports.outstanding') }}" class="btn-elite-ghost">Lihat Detail →</a>
</div>
</div>

{{-- Detail --}}
<div class="grid lg:grid-cols-2 gap-6">
<div class="bg-white border border-rule p-7">
<h3 class="elite-h3 text-lg ink-primary mb-4">SPP per Bulan (12 Bulan)</h3>
<table class="w-full text-sm"><thead><tr class="border-b border-rule">
<th class="text-left py-2 elite-kicker text-[.6rem]">Bulan</th>
<th class="text-right py-2 elite-kicker text-[.6rem]">Total</th>
</tr></thead><tbody>
@forelse($monthlySpp as $m)<tr class="border-b border-rule last:border-0">
<td class="py-2 font-mono">{{ $m->month }}</td>
<td class="py-2 text-right font-mono ink-primary">Rp {{ number_format($m->total/100, 0, ',', '.') }}</td>
</tr>@empty<tr><td colspan="2" class="py-10 text-center text-gray-500 italic">Belum ada pembayaran.</td></tr>@endforelse
</tbody></table>
</div>

<div class="bg-white border border-rule p-7">
<h3 class="elite-h3 text-lg ink-primary mb-4">SPP per Jenis Tagihan (Periode)</h3>
<table class="w-full text-sm"><thead><tr class="border-b border-rule">
<th class="text-left py-2 elite-kicker text-[.6rem]">Jenis</th>
<th class="text-right py-2 elite-kicker text-[.6rem]">Tx</th>
<th class="text-right py-2 elite-kicker text-[.6rem]">Total</th>
</tr></thead><tbody>
@forelse($perStructure as $s)<tr class="border-b border-rule last:border-0">
<td class="py-2 font-serif">{{ $s->structure_name }}</td>
<td class="py-2 text-right font-mono">{{ $s->cnt }}</td>
<td class="py-2 text-right font-mono ink-primary">Rp {{ number_format($s->total/100, 0, ',', '.') }}</td>
</tr>@empty<tr><td colspan="3" class="py-10 text-center text-gray-500 italic">—</td></tr>@endforelse
</tbody></table>
</div>
</div>

<div class="bg-white border border-rule p-7 mt-6">
<h3 class="elite-h3 text-lg ink-primary mb-4">SPP per Metode Pembayaran (Periode)</h3>
<table class="w-full text-sm"><thead><tr class="border-b border-rule">
<th class="text-left py-2 elite-kicker text-[.6rem]">Metode</th>
<th class="text-right py-2 elite-kicker text-[.6rem]">Tx</th>
<th class="text-right py-2 elite-kicker text-[.6rem]">Total</th>
</tr></thead><tbody>
@forelse($perMethod as $m)<tr class="border-b border-rule last:border-0">
<td class="py-2 font-serif">{{ ucfirst(str_replace('_', ' ', $m->payment_method)) }}</td>
<td class="py-2 text-right font-mono">{{ $m->cnt }}</td>
<td class="py-2 text-right font-mono ink-primary">Rp {{ number_format($m->total/100, 0, ',', '.') }}</td>
</tr>@empty<tr><td colspan="3" class="py-10 text-center text-gray-500 italic">—</td></tr>@endforelse
</tbody></table>
</div>

@endsection
