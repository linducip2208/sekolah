@extends('layouts.school-admin')
@section('title', 'ID Gate Devices')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7 flex justify-between items-end"><div>
<div class="elite-kicker mb-2">Portarum Devices</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">ID Gate Devices</h1><div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">Perangkat scanner di gerbang sekolah untuk absensi otomatis.</p></div>
<a href="{{ route('admin.operations.gate-events.index') }}" class="btn-elite-ghost">Log Scan →</a></div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 text-sm text-green-800 border-l-4 border-green-700">{{ session('success') }}</div>@endif

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1"><div class="bg-white border border-rule p-6 sticky top-6">
<form method="POST" action="{{ route('admin.operations.gate-devices.store') }}" class="space-y-3">@csrf
<input name="name" required maxlength="200" placeholder="Nama (mis. Gerbang Utama)" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<input name="location" required maxlength="200" placeholder="Lokasi" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<select name="type" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="entry">Masuk saja</option><option value="exit">Keluar saja</option><option value="both">Dua arah</option>
</select>
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Daftarkan</button>
</form></div></div>

<div class="lg:col-span-2"><div class="bg-white border border-rule overflow-hidden">
<table class="w-full text-sm"><thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Lokasi</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tipe</th>
<th></th></tr></thead><tbody>
@forelse($devices as $d)<tr class="border-t border-rule">
<td class="px-4 py-3 font-serif font-semibold">{{ $d->name }}</td>
<td class="px-4 py-3 text-xs">{{ $d->location }}</td>
<td class="px-4 py-3"><span class="elite-kicker text-[.55rem]">{{ $d->type }}</span></td>
<td class="px-4 py-3 text-right"><form method="POST" action="{{ route('admin.operations.gate-devices.destroy', $d) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form></td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada device.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection
