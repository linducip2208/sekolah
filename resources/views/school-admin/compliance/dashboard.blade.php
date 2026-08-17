@extends('layouts.school-admin')
@section('title', 'Dashboard Kepatuhan')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Compliantia</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Dashboard Kepatuhan</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">Ringkasan akreditasi, adiwiyata, audit internal, dan rencana perbaikan.</p>
</div>

<div class="grid md:grid-cols-3 gap-6 mb-6">
    {{-- Akreditasi --}}
    <div class="bg-white border border-rule p-5">
        <div class="elite-kicker text-[.7rem] mb-2">Akreditasi</div>
        <div class="flex items-end gap-2">
            <div class="font-display text-4xl ink-primary">{{ $predictedScore }}</div>
            <div class="font-display text-2xl mb-1" style="color:{{ $grade['color'] }}">{{ $grade['grade'] }}</div>
        </div>
        <div class="text-xs text-gray-500 mt-1">{{ $grade['label'] }} · prediksi nilai</div>
        <a href="{{ route('admin.accreditation.dashboard') }}" class="text-xs underline ink-secondary mt-2 inline-block">Buka akreditasi →</a>
    </div>

    {{-- Adiwiyata --}}
    <div class="bg-white border border-rule p-5">
        <div class="elite-kicker text-[.7rem] mb-2">Adiwiyata</div>
        <div class="font-display text-2xl ink-primary">{{ $level?->achieved_level ?? '—' }}</div>
        <div class="text-xs text-gray-500 mt-1">Level tercapai</div>
        <div class="text-xs text-gray-500 mt-2">{{ $verifiedCount }}/{{ $evidenceCount }} bukti terverifikasi · {{ $indicatorCount }} indikator</div>
        <a href="{{ route('admin.adiwiyata.dashboard') }}" class="text-xs underline ink-secondary mt-2 inline-block">Buka adiwiyata →</a>
    </div>

    {{-- Audit Internal --}}
    <div class="bg-white border border-rule p-5">
        <div class="elite-kicker text-[.7rem] mb-2">Audit Internal</div>
        <div class="flex gap-4 text-sm mt-1">
            <div><div class="font-display text-2xl text-amber-700">{{ $auditSummary['open'] }}</div><div class="text-xs text-gray-500">Terbuka</div></div>
            <div><div class="font-display text-2xl text-green-700">{{ $auditSummary['resolved'] }}</div><div class="text-xs text-gray-500">Selesai</div></div>
            <div><div class="font-display text-2xl text-red-700">{{ $auditSummary['high'] }}</div><div class="text-xs text-gray-500">Kritis</div></div>
        </div>
        <a href="{{ route('admin.internal-audit.index') }}" class="text-xs underline ink-secondary mt-2 inline-block">Buka audit →</a>
    </div>
</div>

{{-- Rencana Perbaikan --}}
<div class="bg-white border border-rule overflow-hidden">
    <div class="px-4 py-3 bg-gray-50 border-b border-rule elite-kicker text-[.7rem]">Rencana Perbaikan Akreditasi</div>
    <div class="grid grid-cols-3 divide-x divide-rule text-center">
        <div class="p-4"><div class="font-display text-3xl ink-primary">{{ ($plans['pending'] ?? collect())->count() }}</div><div class="text-xs text-gray-500">Pending</div></div>
        <div class="p-4"><div class="font-display text-3xl text-amber-700">{{ ($plans['in_progress'] ?? collect())->count() }}</div><div class="text-xs text-gray-500">Berjalan</div></div>
        <div class="p-4"><div class="font-display text-3xl text-green-700">{{ ($plans['completed'] ?? collect())->count() }}</div><div class="text-xs text-gray-500">Selesai</div></div>
    </div>
</div>

@endsection
