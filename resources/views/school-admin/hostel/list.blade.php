@extends('layouts.school-admin')
@section('title', 'Asrama')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7 flex justify-between items-end"><div>
<div class="elite-kicker mb-2">Domus Habitationis</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Asrama / Hostel</h1><div class="elite-rule"></div></div>
<a href="{{ route('admin.hostel.allocations.index') }}" class="btn-elite-ghost">Alokasi Kamar →</a></div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1"><div class="bg-white border border-rule p-6 sticky top-6">
<form method="POST" action="{{ route('admin.hostel.list.store') }}" class="space-y-3">@csrf
<input name="name" required maxlength="200" placeholder="Asrama Putra A" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<select name="type" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="boys">Putra</option><option value="girls">Putri</option><option value="mixed">Campuran</option>
</select>
<input name="warden_name" maxlength="200" placeholder="Nama warden/pengasuh" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan</button>
</form></div></div>

<div class="lg:col-span-2"><div class="bg-white border border-rule overflow-hidden">
<table class="w-full text-sm"><thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tipe</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Warden</th>
<th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Kamar</th>
<th></th></tr></thead><tbody>
@forelse($hostels as $h)<tr class="border-t border-rule">
<td class="px-4 py-3 font-serif font-semibold">{{ $h->name }}</td>
<td class="px-4 py-3"><span class="elite-kicker text-[.55rem]">{{ $h->type }}</span></td>
<td class="px-4 py-3 text-xs">{{ $h->warden_name ?? '—' }}</td>
<td class="px-4 py-3 text-center"><a href="{{ route('admin.hostel.rooms.index', $h) }}" class="text-xs underline ink-secondary hover:ink-accent">{{ $h->rooms_count }} kamar →</a></td>
<td class="px-4 py-3 text-right"><form method="POST" action="{{ route('admin.hostel.list.destroy', $h) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form></td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada asrama.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection
