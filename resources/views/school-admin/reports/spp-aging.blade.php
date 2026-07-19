@extends('layouts.school-admin')
@section('title', 'SPP Aging')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><h1 class="elite-h1 text-3xl ink-primary mb-2">SPP Aging Report</h1><div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">Klasifikasi tunggakan SPP berdasarkan umur (30/60/90 hari).</p></div>

<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
@foreach([
    ['current', 'Belum Lewat', 'green'],
    ['30d', '0-30 Hari', 'yellow'],
    ['60d', '31-60 Hari', 'orange'],
    ['90d', '61-90 Hari', 'red'],
    ['90plus', '> 90 Hari', 'purple'],
] as [$key, $label, $color])
<div class="bg-white border-l-4 border-{{ $color }}-600 p-4">
<div class="elite-kicker text-[.55rem]">{{ $label }}</div>
<div class="font-display text-lg ink-primary mt-1">Rp {{ number_format($aging[$key]/100, 0, ',', '.') }}</div>
<div class="text-xs text-gray-500">{{ $countAging[$key] }} invoice</div>
</div>
@endforeach
</div>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Invoice</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Jatuh Tempo</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Hari Lewat</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Sisa</th>
</tr></thead><tbody>
@php $now = now(); @endphp
@forelse($rows as $r)
@php
    $sisa = $r->amount - $r->paid_amount;
    $daysOver = $now->diffInDays(\Carbon\Carbon::parse($r->due_date), false);
    $overdue = -$daysOver;
@endphp
<tr class="border-t border-rule {{ $overdue > 30 ? 'bg-red-50' : '' }}">
<td class="px-3 py-3 font-mono text-xs">{{ $r->invoice_no }}</td>
<td class="px-3 py-3"><div class="font-serif font-semibold">{{ $r->student_name }}</div><div class="text-xs text-gray-500">{{ $r->admission_no }}</div></td>
<td class="px-3 py-3 text-xs">{{ \Carbon\Carbon::parse($r->due_date)->format('d M Y') }}</td>
<td class="px-3 py-3 text-right font-mono {{ $overdue > 60 ? 'text-red-700 font-bold' : ($overdue > 30 ? 'text-orange-700' : '') }}">{{ $overdue > 0 ? $overdue.' hari' : 'belum' }}</td>
<td class="px-3 py-3 text-right font-mono">Rp {{ number_format($sisa/100, 0, ',', '.') }}</td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">🎉 Tidak ada tunggakan!</td></tr>@endforelse
</tbody></table></div>
@endsection
