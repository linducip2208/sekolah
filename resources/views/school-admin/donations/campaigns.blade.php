@extends('layouts.school-admin')
@section('title', 'Campaign Donasi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7 flex justify-between items-end"><div>
<div class="elite-kicker mb-2">Stipes Donationis</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Campaign Donasi</h1><div class="elite-rule"></div></div>
<a href="{{ route('admin.donations.list') }}" class="btn-elite-ghost">Daftar Donatur →</a></div>

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Buat Campaign</summary>
<form method="POST" action="{{ route('admin.donations.campaigns.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-2 gap-3">@csrf
<input name="title" required maxlength="200" placeholder="Judul campaign" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
<input name="slug" required pattern="[a-z0-9\-]+" maxlength="200" placeholder="slug-url" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<select name="category" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="general">Umum</option><option value="building">Pembangunan</option>
<option value="emergency">Darurat</option><option value="scholarship">Beasiswa</option>
</select>
<input type="number" step="100000" min="0" name="target_rupiah" required placeholder="Target (Rp)" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<input type="date" name="start_date" required class="border-2 border-rule px-3 py-2 text-sm">
<input type="date" name="end_date" required class="md:col-span-2 border-2 border-rule px-3 py-2 text-sm">
<textarea name="description" rows="3" required maxlength="2000" placeholder="Deskripsi campaign" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
<div class="md:col-span-2"><button class="btn-elite">Buat Campaign</button></div>
</form></details>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
@forelse($campaigns as $c)
<div class="elite-card p-6">
<div class="flex justify-between items-start mb-2">
<h3 class="elite-h3 text-base ink-primary">{{ $c->title }}</h3>
<span class="elite-kicker text-[.55rem]">{{ $c->status }}</span></div>
<div class="elite-kicker text-[.6rem] mb-3" style="color:var(--c-muted);">{{ $c->category }}</div>
<p class="font-serif text-xs text-gray-700 mb-3">{{ Str::limit($c->description, 100) }}</p>
@php $pct = $c->progressPercent(); @endphp
<div class="text-xs mb-1">Rp {{ number_format(($c->raised_actual ?? $c->raised_amount)/100, 0, ',', '.') }} / Rp {{ number_format($c->target_amount/100, 0, ',', '.') }}</div>
<div class="bg-gray-200 h-2 rounded"><div style="width:{{ min($pct,100) }}%; background:var(--c-accent);" class="h-full rounded"></div></div>
<div class="mt-3 flex justify-between items-center">
<span class="text-xs text-gray-500">{{ $c->start_date?->format('d M') }} → {{ $c->end_date?->format('d M Y') }}</span>
<form method="POST" action="{{ route('admin.donations.campaigns.destroy', $c) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
</div>
</div>
@empty<div class="md:col-span-2 lg:col-span-3 bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada campaign.</div>
@endforelse
</div>
<div class="mt-4">{{ $campaigns->links() }}</div>
@endsection
