@extends('layouts.school-admin')
@section('title', 'Tunggakan SPP')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.finance.reports.summary') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Laporan Keuangan</a>

<div class="mb-7"><div class="elite-kicker mb-2">Debita</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Tunggakan SPP / Invoice Belum Lunas</h1>
<div class="elite-rule"></div>
<p class="font-serif text-base text-gray-600 mt-3">{{ $invoices->total() }} invoice belum lunas atau overdue.</p></div>

<div class="bg-white border-l-4 border-yellow-700 p-5 mb-6" style="background:rgba(234,179,8,.06);">
<div class="elite-kicker text-[.65rem] mb-1" style="color:#a16207;">Total Sisa Tunggakan</div>
<div class="font-display text-3xl ink-primary">Rp {{ number_format($totalOutstanding/100, 0, ',', '.') }}</div>
</div>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Invoice</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Kelas</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Jenis</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Periode</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Jatuh Tempo</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Tagihan</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Sisa</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
<th></th>
</tr></thead><tbody>
@forelse($invoices as $i)
@php
  $sisa = $i->amount - $i->paid_amount;
  $isOverdue = \Carbon\Carbon::parse($i->due_date)->isPast() && $i->status !== 'paid';
@endphp
<tr class="border-t border-rule {{ $isOverdue ? 'bg-red-50' : '' }} hover:bg-gray-50">
<td class="px-3 py-3 font-mono text-xs">{{ $i->invoice_no }}</td>
<td class="px-3 py-3"><div class="font-serif font-semibold">{{ $i->student_name }}</div><div class="text-xs text-gray-500">{{ $i->admission_no }}</div></td>
<td class="px-3 py-3 text-xs">{{ $i->class_name }} {{ $i->section_name }}</td>
<td class="px-3 py-3 text-xs">{{ $i->fee_name }}</td>
<td class="px-3 py-3 font-mono text-xs">{{ $i->period }}</td>
<td class="px-3 py-3 text-xs {{ $isOverdue ? 'text-red-700 font-semibold' : '' }}">{{ \Carbon\Carbon::parse($i->due_date)->format('d M Y') }}</td>
<td class="px-3 py-3 text-right font-mono text-xs">{{ number_format($i->amount/100, 0, ',', '.') }}</td>
<td class="px-3 py-3 text-right font-mono font-semibold text-red-700">{{ number_format($sisa/100, 0, ',', '.') }}</td>
<td class="px-3 py-3">
<span class="text-xs px-2 py-0.5 rounded
{{ $i->status === 'unpaid' ? 'bg-gray-100 text-gray-700' : '' }}
{{ $i->status === 'partial' ? 'bg-yellow-100 text-yellow-700' : '' }}
{{ $i->status === 'overdue' ? 'bg-red-100 text-red-700' : '' }}">{{ $i->status }}</span>
</td>
<td class="px-3 py-3 text-right">
<a href="{{ route('admin.fee.invoices.show', $i->id) }}" class="elite-kicker text-xs ink-secondary hover:ink-accent">Detail →</a>
</td></tr>
@empty<tr><td colspan="10" class="p-10 text-center text-gray-500 italic font-serif">🎉 Tidak ada tunggakan! Semua invoice sudah lunas.</td></tr>@endforelse
</tbody></table></div>

<div class="mt-4">{{ $invoices->links() }}</div>

@endsection
