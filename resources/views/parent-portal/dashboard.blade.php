@extends('layouts.parent')
@section('title', 'Beranda Wali')
@section('content')

<div class="mb-8">
    <div class="elite-kicker mb-2">Salutatio</div>
    <h1 class="elite-h1 text-4xl ink-primary mb-2">Selamat datang, {{ auth()->user()->name }}</h1>
    <div class="elite-rule mb-3"></div>
    <p class="font-serif text-lg" style="color: var(--c-muted);">Pantau perkembangan anak Anda di sini.</p>
</div>

@if($outstandingTotal > 0)
<div class="bg-white border-l-4 border-red-700 p-5 mb-6">
<div class="flex justify-between items-center">
<div>
<div class="elite-kicker text-[.65rem] mb-1" style="color:#b91c1c;">⚠ Tagihan Belum Dibayar</div>
<div class="font-display text-2xl ink-primary">Rp {{ number_format($outstandingTotal/100, 0, ',', '.') }}</div>
</div>
<a href="{{ route('portal.invoices') }}" class="btn-elite-gold">Bayar Sekarang →</a>
</div>
</div>
@endif

<h2 class="elite-h2 text-2xl ink-primary mb-4">Anak Anda ({{ $children->count() }})</h2>

@if($children->isEmpty())
<div class="bg-white border border-rule p-10 text-center">
<p class="font-serif text-base text-gray-600 italic mb-2">Belum ada data anak yang terkait dengan akun Anda.</p>
<p class="font-serif text-sm text-gray-500">Hubungi Tata Usaha sekolah untuk linking data orang tua-anak.</p>
</div>
@else
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
@foreach($children as $c)
<a href="{{ route('portal.child', $c) }}" class="elite-card p-7 group block">
<div class="elite-kicker text-[.6rem] mb-2" style="color: var(--c-muted);">NIS {{ $c->admission_no }}</div>
<h3 class="elite-h3 text-xl ink-primary mb-2">{{ $c->user?->name }}</h3>
<div class="text-sm text-gray-600 mb-3">{{ $c->classSection?->classRoom?->name }} {{ $c->classSection?->section?->name }}</div>
<div class="w-12 h-px bg-[var(--c-accent)] mb-3 group-hover:w-20 transition-all"></div>
<div class="elite-kicker text-[.6rem]" style="color: var(--c-accent);">Lihat Detail →</div>
</a>
@endforeach
</div>
@endif

<div class="mt-12 grid md:grid-cols-2 lg:grid-cols-4 gap-4">
<a href="{{ route('portal.invoices') }}" class="bg-white border border-rule p-5 hover:border-[var(--c-accent)] block">
<div class="elite-kicker text-[.6rem] mb-1">💳 Pembayaran</div>
<div class="font-serif text-base ink-primary">Tagihan SPP & Bayar</div>
</a>
<a href="{{ route('portal.conferences') }}" class="bg-white border border-rule p-5 hover:border-[var(--c-accent)] block">
<div class="elite-kicker text-[.6rem] mb-1">👨‍🏫 Konferensi</div>
<div class="font-serif text-base ink-primary">Booking Orang Tua-Guru</div>
</a>
<a href="{{ route('forum.index') }}" class="bg-white border border-rule p-5 hover:border-[var(--c-accent)] block">
<div class="elite-kicker text-[.6rem] mb-1">💬 Komunitas</div>
<div class="font-serif text-base ink-primary">Forum Diskusi</div>
</a>
<a href="/docs/parent" class="bg-white border border-rule p-5 hover:border-[var(--c-accent)] block">
<div class="elite-kicker text-[.6rem] mb-1">📖 Bantuan</div>
<div class="font-serif text-base ink-primary">Buku Panduan Wali</div>
</a>
</div>

@endsection
