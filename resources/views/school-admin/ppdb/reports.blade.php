@extends('layouts.school-admin')
@section('title', 'Laporan PPDB')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7"><div class="elite-kicker mb-2">Analytics</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Laporan & Analitik PPDB</h1>
<div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">Ringkasan funnel pendaftaran PPDB sekolah.</p></div>

<form method="GET" class="bg-white border border-rule p-5 mb-6 grid grid-cols-1 md:grid-cols-3 gap-3">
<select name="period_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— Semua Periode —</option>
@foreach($periods as $p)<option value="{{ $p->id }}" @selected(request('period_id') == $p->id)>{{ $p->name }}</option>@endforeach
</select>
<button class="btn-elite" style="padding:.6rem 1rem;font-size:.65rem;">Filter</button>
</function>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
@foreach($reports['by_status'] as $status => $count)
<div class="bg-white border border-rule p-4 text-center">
    <div class="elite-kicker text-[.55rem] mb-1">{{ ucfirst($status) }}</div>
    <div class="text-3xl font-extrabold ink-primary font-mono">{{ $count }}</div>
</div>
@endforeach
</div>

{{-- Conversion Funnel --}}
<div class="bg-white border border-rule p-6 mb-8">
<h2 class="font-serif font-bold text-lg ink-primary mb-4">Konversi Funnel</h2>
<div class="space-y-3">
@php
$funnel = [
    'Draft → Submitted'        => $reports['conversion_rates']['draft_to_submitted'] ?? 0,
    'Submitted → Verified'     => $reports['conversion_rates']['submitted_to_verified'] ?? 0,
    'Verified → Accepted'      => $reports['conversion_rates']['verified_to_accepted'] ?? 0,
    'Accepted → Enrolled'      => $reports['conversion_rates']['accepted_to_enrolled'] ?? 0,
    'Overall Enrollment Rate'  => $reports['conversion_rates']['overall_enrollment'] ?? 0,
];
@endphp
@foreach($funnel as $label => $rate)
<div class="flex items-center gap-4">
    <div class="w-48 text-xs font-serif text-gray-700">{{ $label }}</div>
    <div class="flex-1 bg-gray-100 rounded-full h-5 overflow-hidden">
        <div class="bg-[var(--c-primary)] h-full rounded-full transition-all" style="width: {{ $rate }}%"></div>
    </div>
    <div class="w-14 text-right font-mono text-sm font-bold ink-primary">{{ $rate }}%</div>
</div>
@endforeach
</div>
</div>

{{-- Per-Jalur Breakdown --}}
<div class="bg-white border border-rule overflow-hidden mb-8">
<h2 class="font-serif font-bold text-lg ink-primary p-6 pb-0">Breakdown per Jalur</h2>
<table class="w-full text-sm mt-4">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Jalur</th>
<th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Total</th>
<th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Draft</th>
<th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Submitted</th>
<th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Verified</th>
<th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Accepted</th>
<th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Rejected</th>
<th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Enrolled</th>
</tr></thead><tbody>
@foreach($reports['by_jalur'] as $jalur => $data)
<tr class="border-t border-rule hover:bg-gray-50">
<td class="px-4 py-3 font-serif font-semibold">{{ ucfirst($jalur) }}</td>
<td class="px-4 py-3 text-right font-mono font-bold">{{ $data['total'] }}</td>
<td class="px-4 py-3 text-right font-mono">{{ $data['draft'] }}</td>
<td class="px-4 py-3 text-right font-mono">{{ $data['submitted'] }}</td>
<td class="px-4 py-3 text-right font-mono">{{ $data['verified'] }}</td>
<td class="px-4 py-3 text-right font-mono text-green-700">{{ $data['accepted'] }}</td>
<td class="px-4 py-3 text-right font-mono text-red-600">{{ $data['rejected'] }}</td>
<td class="px-4 py-3 text-right font-mono text-blue-700 font-bold">{{ $data['enrolled'] }}</td>
</tr>
@endforeach
</tbody></table>
</div>

{{-- Total Summary --}}
<div class="bg-white border border-rule p-6">
<div class="flex items-center justify-between">
    <div>
        <div class="font-serif font-bold text-lg ink-primary">Total Pendaftar</div>
        <div class="text-xs text-gray-500 font-serif">Semua periode</div>
    </div>
    <div class="text-4xl font-extrabold ink-primary font-mono">{{ $reports['total'] }}</div>
</div>
</div>

@endsection
