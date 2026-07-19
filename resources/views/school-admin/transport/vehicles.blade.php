@extends('layouts.school-admin')
@section('title', 'Kendaraan')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7 flex justify-between items-end"><div>
<div class="elite-kicker mb-2">Vehicula</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Kendaraan / Bus Sekolah</h1>
<div class="elite-rule"></div></div>
<a href="{{ route('admin.transport.routes.index') }}" class="btn-elite-ghost">Rute →</a></div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1"><div class="bg-white border border-rule p-6 sticky top-6">
<h3 class="elite-h3 text-base ink-primary mb-3">Tambah Kendaraan</h3>
<form method="POST" action="{{ route('admin.transport.vehicles.store') }}" class="space-y-3">@csrf
<input name="registration_no" required maxlength="30" placeholder="B 1234 ABC" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
<input name="make_model" maxlength="200" placeholder="Mitsubishi Colt Diesel" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<input type="number" name="capacity" required min="1" max="100" placeholder="Kapasitas (orang)" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
<input name="driver_name" placeholder="Nama supir" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<input name="driver_phone" maxlength="30" placeholder="HP supir" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan</button>
</form></div></div>

<div class="lg:col-span-2"><div class="bg-white border border-rule overflow-hidden">
<table class="w-full text-sm"><thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">No. Pol</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Model</th>
<th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Kapasitas</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Supir</th>
<th></th></tr></thead><tbody>
@forelse($vehicles as $v)<tr class="border-t border-rule">
<td class="px-4 py-3 font-mono font-semibold">{{ $v->registration_no }}</td>
<td class="px-4 py-3 text-xs">{{ $v->make_model ?? '—' }}</td>
<td class="px-4 py-3 text-center font-mono">{{ $v->capacity }}</td>
<td class="px-4 py-3 text-xs">{{ $v->driver_name ?? '—' }}<br><span class="text-gray-500">{{ $v->driver_phone }}</span></td>
<td class="px-4 py-3 text-right"><form method="POST" action="{{ route('admin.transport.vehicles.destroy', $v) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form></td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada kendaraan.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection
