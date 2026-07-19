@extends('super-admin.layout')
@section('title', 'Laporan Keuangan Platform')
@section('content')

<div class="mb-7"><div class="elite-kicker mb-2">Tabulationes Financiariae · Pemilik Platform</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Laporan Keuangan Platform</h1><div class="elite-rule"></div>
<p class="font-serif text-base text-gray-600 mt-3">Pemasukan dari sekolah-sekolah yang berlangganan ke platform Anda.</p></div>

<form method="GET" class="bg-white border border-rule p-5 mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
<div><label class="elite-kicker text-[.6rem] block mb-1">Dari</label>
<input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Sampai</label>
<input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
<div class="flex items-end gap-2">
<button class="btn-elite" style="padding:.6rem 1rem;font-size:.65rem;">Filter</button>
<a href="{{ route('super.reports.export', request()->all()) }}" class="btn-elite-gold" style="padding:.6rem 1rem;font-size:.65rem;">⤓ Export CSV</a>
</div>
</form>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
<div class="bg-white border-l-4 border-purple-600 p-5">
<div class="elite-kicker text-[.6rem]">Total Revenue (Lifetime)</div>
<div class="font-display text-2xl ink-primary mt-2">Rp {{ number_format($totalRevenue/100, 0, ',', '.') }}</div>
</div>
<div class="bg-white border-l-4 border-blue-600 p-5">
<div class="elite-kicker text-[.6rem]">Revenue Periode</div>
<div class="font-display text-2xl ink-primary mt-2">Rp {{ number_format($rangeRevenue/100, 0, ',', '.') }}</div>
<div class="text-xs text-gray-500 mt-1">{{ $from->format('d M Y') }} → {{ $to->format('d M Y') }}</div>
</div>
<div class="bg-white border-l-4 border-green-600 p-5">
<div class="elite-kicker text-[.6rem]">Bulan Lalu</div>
<div class="font-display text-2xl ink-primary mt-2">Rp {{ number_format($lastMonthRev/100, 0, ',', '.') }}</div>
</div>
<div class="bg-white border-l-4 border-yellow-600 p-5">
<div class="elite-kicker text-[.6rem]">ARPU</div>
<div class="font-display text-2xl ink-primary mt-2">Rp {{ number_format($arpu/100, 0, ',', '.') }}</div>
<div class="text-xs text-gray-500 mt-1">Lifetime / active school</div>
</div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
<div class="bg-white border-l-4 border-indigo-600 p-5">
<div class="elite-kicker text-[.6rem]">Total Sekolah</div>
<div class="font-display text-2xl ink-primary mt-2">{{ $totalSchools }} <span class="text-sm text-gray-500">/ {{ $activeSchools }} aktif</span></div>
</div>
<div class="bg-white border-l-4 border-teal-600 p-5">
<div class="elite-kicker text-[.6rem]">Pendaftar Belum Aktif</div>
<div class="font-display text-xl ink-primary mt-2">Rp {{ number_format($pendingReg/100, 0, ',', '.') }}</div>
<div class="text-xs text-gray-500 mt-1">Potensi dari pendaftaran pending</div>
</div>
<div class="bg-white border-l-4 border-pink-600 p-5">
<div class="elite-kicker text-[.6rem]">Conversion Rate</div>
<div class="font-display text-2xl ink-primary mt-2">{{ $conversionRate }}%</div>
<div class="text-xs text-gray-500 mt-1">{{ $regActivated }} aktif / {{ $regTotal }} pendaftar</div>
</div>
</div>

<div class="grid lg:grid-cols-2 gap-6 mb-6">
<div class="bg-white border border-rule p-7">
<h3 class="elite-h3 text-lg ink-primary mb-4">Revenue per Bulan</h3>
<table class="w-full text-sm"><thead><tr class="border-b border-rule">
<th class="text-left py-2 elite-kicker text-[.6rem]">Bulan</th>
<th class="text-right py-2 elite-kicker text-[.6rem]">Tx</th>
<th class="text-right py-2 elite-kicker text-[.6rem]">Total</th>
</tr></thead><tbody>
@forelse($monthlyRev as $row)<tr class="border-b border-rule last:border-0">
<td class="py-2 font-mono">{{ $row->month }}</td>
<td class="py-2 text-right font-mono">{{ $row->cnt }}</td>
<td class="py-2 text-right font-mono ink-primary">Rp {{ number_format($row->total/100, 0, ',', '.') }}</td>
</tr>@empty<tr><td colspan="3" class="py-10 text-center text-gray-500 italic">Belum ada transaksi.</td></tr>@endforelse
</tbody></table>
</div>

<div class="bg-white border border-rule p-7">
<h3 class="elite-h3 text-lg ink-primary mb-4">Revenue per Plan</h3>
<table class="w-full text-sm"><thead><tr class="border-b border-rule">
<th class="text-left py-2 elite-kicker text-[.6rem]">Plan</th>
<th class="text-right py-2 elite-kicker text-[.6rem]">Tx</th>
<th class="text-right py-2 elite-kicker text-[.6rem]">Total</th>
</tr></thead><tbody>
@forelse($perPlan as $p)<tr class="border-b border-rule last:border-0">
<td class="py-2 font-serif">{{ $p->plan_name ?? '—' }}</td>
<td class="py-2 text-right font-mono">{{ $p->cnt }}</td>
<td class="py-2 text-right font-mono ink-primary">Rp {{ number_format($p->total/100, 0, ',', '.') }}</td>
</tr>@empty<tr><td colspan="3" class="py-10 text-center text-gray-500 italic">Belum ada data.</td></tr>@endforelse
</tbody></table>
</div>
</div>

<div class="bg-white border border-rule p-7 mb-6">
<h3 class="elite-h3 text-lg ink-primary mb-4">Top 10 Sekolah by Revenue (Lifetime)</h3>
<table class="w-full text-sm"><thead><tr class="border-b border-rule">
<th class="text-left py-2 elite-kicker text-[.6rem]">#</th>
<th class="text-left py-2 elite-kicker text-[.6rem]">Sekolah</th>
<th class="text-right py-2 elite-kicker text-[.6rem]">Tx</th>
<th class="text-right py-2 elite-kicker text-[.6rem]">Total Bayar</th>
</tr></thead><tbody>
@forelse($topSchools as $i => $s)<tr class="border-b border-rule last:border-0">
<td class="py-2 font-display text-lg ink-accent">{{ $i+1 }}</td>
<td class="py-2"><div class="font-serif font-semibold">{{ $s->name }}</div><div class="text-xs text-gray-500 font-mono">{{ $s->subdomain }}</div></td>
<td class="py-2 text-right font-mono">{{ $s->cnt }}</td>
<td class="py-2 text-right font-mono ink-primary">Rp {{ number_format($s->total/100, 0, ',', '.') }}</td>
</tr>@empty<tr><td colspan="4" class="py-10 text-center text-gray-500 italic">Belum ada sekolah.</td></tr>@endforelse
</tbody></table>
</div>

<div class="bg-white border border-rule p-7">
<h3 class="elite-h3 text-lg ink-primary mb-4">20 Transaksi Terbaru</h3>
<div class="overflow-x-auto"><table class="w-full text-sm">
<thead class="bg-gray-50"><tr>
<th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Sekolah</th>
<th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Plan</th>
<th class="text-right px-3 py-2 elite-kicker text-[.6rem]">Jumlah</th>
<th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Metode</th>
<th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Ref</th>
</tr></thead><tbody>
@forelse($recentTx as $tx)<tr class="border-t border-rule">
<td class="px-3 py-2 text-xs">{{ \Carbon\Carbon::parse($tx->created_at)->format('d M Y H:i') }}</td>
<td class="px-3 py-2 font-serif">{{ $tx->school_name }}</td>
<td class="px-3 py-2 text-xs">{{ $tx->plan_name }}</td>
<td class="px-3 py-2 text-right font-mono ink-primary">Rp {{ number_format($tx->amount/100, 0, ',', '.') }}</td>
<td class="px-3 py-2 text-xs"><span class="elite-kicker text-[.55rem]">{{ $tx->payment_method ?? '—' }}</span></td>
<td class="px-3 py-2 font-mono text-xs">{{ $tx->reference ?? '—' }}</td>
</tr>@empty<tr><td colspan="6" class="py-8 text-center text-gray-500 italic">Belum ada transaksi.</td></tr>@endforelse
</tbody></table></div>
</div>

@endsection
