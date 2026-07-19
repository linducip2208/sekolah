@extends('layouts.school-admin')
@section('title', 'Rute Transport')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.transport.vehicles.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kendaraan</a>

<div class="mb-7"><div class="elite-kicker mb-2">Itinera</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Rute Bus / Antar Jemput</h1>
<div class="elite-rule"></div></div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1"><div class="bg-white border border-rule p-6 sticky top-6">
<form method="POST" action="{{ route('admin.transport.routes.store') }}" class="space-y-3">@csrf
<input name="name" required maxlength="200" placeholder="Rute A — Kelapa Gading" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<div><label class="elite-kicker text-[.6rem] block mb-1">Biaya/Bulan (Rp)</label>
<input type="number" min="0" step="1000" name="fee_per_month_rupiah" required class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" placeholder="350000"></div>
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan Rute</button>
</form></div></div>

<div class="lg:col-span-2"><div class="bg-white border border-rule overflow-hidden">
<table class="w-full text-sm"><thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama Rute</th>
<th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Biaya/Bulan</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
<th></th></tr></thead><tbody>
@forelse($routes as $r)<tr class="border-t border-rule">
<td class="px-4 py-3 font-serif font-semibold">{{ $r->name }}</td>
<td class="px-4 py-3 text-right font-mono">Rp {{ number_format($r->fee_per_month/100, 0, ',', '.') }}</td>
<td class="px-4 py-3"><span class="text-xs {{ $r->is_active ? 'text-green-700' : 'text-gray-500' }}">{{ $r->is_active ? '● Aktif' : 'Off' }}</span></td>
<td class="px-4 py-3 text-right"><form method="POST" action="{{ route('admin.transport.routes.destroy', $r) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form></td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada rute.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection
