@extends('layouts.parent')
@section('title', 'Beranda')
@section('content')

@php
    $hour = (int) now()->format('G');
    $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
@endphp

<div class="mb-7">
    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-1" style="color: var(--color-text);">{{ $greeting }}, {{ Str::before(auth()->user()->name, ' ') }}</h1>
    <p class="text-sm" style="color: var(--color-text-secondary);">Pantau perkembangan anak Anda — {{ now()->translatedFormat('l, d F Y') }}.</p>
</div>

@if($outstandingTotal > 0)
    <div class="mb-6">
        <x-ui.alert-card tone="danger"
            title="Tagihan belum dibayar"
            description="Total tunggakan Rp {{ number_format($outstandingTotal/100, 0, ',', '.') }} — segera lunasi agar pembelajaran anak tidak terganggu."
            ctaLabel="Bayar Sekarang"
            :ctaHref="route('portal.invoices')" />
    </div>
@endif

<h2 class="text-lg font-bold mb-3" style="color: var(--color-text);">Anak Anda ({{ $children->count() }})</h2>

@if($children->isEmpty())
    <div class="card mb-8">
        <x-feedback.empty-state icon="user" title="Belum ada data anak" description="Hubungi Tata Usaha sekolah untuk mengaitkan akun ini dengan data anak Anda.">
            <a href="{{ rescue(fn () => route('portal.conferences'), '#', false) }}" class="btn btn-sm">Hubungi via Konferensi</a>
        </x-feedback.empty-state>
    </div>
@else
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        @foreach($children as $c)
            <a href="{{ route('portal.child', $c) }}" class="card card-pad card-hover block group">
                <div class="flex items-center gap-3">
                    <span class="avatar avatar-lg" aria-hidden="true">{{ Str::upper(Str::substr($c->user?->name ?? '?', 0, 1)) }}</span>
                    <div class="min-w-0">
                        <div class="text-sm font-bold truncate" style="color: var(--color-text);">{{ $c->user?->name }}</div>
                        <div class="text-xs mt-0.5" style="color: var(--color-text-muted);">NIS {{ $c->admission_no }}</div>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-4 pt-3 border-t" style="border-color: var(--color-border);">
                    <span class="text-xs font-medium" style="color: var(--color-text-secondary);">{{ $c->classSection?->classRoom?->name }} {{ $c->classSection?->section?->name }}</span>
                    <span class="inline-flex items-center gap-1 text-xs font-bold" style="color: var(--color-primary);">Detail
                        <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </div>
            </a>
        @endforeach
    </div>
@endif

{{-- Ringkasan cepat --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    <a href="{{ route('portal.invoices') }}" class="card card-pad card-hover">
        <x-ui.icon name="money" class="w-5 h-5 mb-2 text-[var(--color-primary)]" />
        <div class="text-[12px] font-medium" style="color: var(--color-text-secondary);">Tagihan SPP</div>
        <div class="text-base font-extrabold mt-0.5" style="color: var(--color-text);">@if($outstandingTotal > 0)Rp {{ number_format($outstandingTotal/100, 0, ',', '.') }}@else Lunas @endif</div>
    </a>
    <a href="{{ rescue(fn () => route('portal.conferences'), '#', false) }}" class="card card-pad card-hover">
        <x-ui.icon name="users" class="w-5 h-5 mb-2 text-[var(--color-info)]" />
        <div class="text-[12px] font-medium" style="color: var(--color-text-secondary);">Konferensi Guru</div>
        <div class="text-base font-extrabold mt-0.5" style="color: var(--color-text);">Booking</div>
    </a>
    <a href="{{ route('forum.index') }}" class="card card-pad card-hover">
        <x-ui.icon name="inbox" class="w-5 h-5 mb-2 text-[var(--color-accent)]" />
        <div class="text-[12px] font-medium" style="color: var(--color-text-secondary);">Komunitas</div>
        <div class="text-base font-extrabold mt-0.5" style="color: var(--color-text);">Forum Diskusi</div>
    </a>
    <a href="/docs/parent" class="card card-pad card-hover">
        <x-ui.icon name="question" class="w-5 h-5 mb-2 text-[var(--color-success)]" />
        <div class="text-[12px] font-medium" style="color: var(--color-text-secondary);">Bantuan</div>
        <div class="text-base font-extrabold mt-0.5" style="color: var(--color-text);">Panduan Portal</div>
    </a>
</div>

@endsection
