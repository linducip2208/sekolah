@extends('layouts.school-admin')
@section('title', 'Kamar Asrama')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<a href="{{ route('admin.hostel.list.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Asrama</a>
<div class="mb-7"><div class="elite-kicker mb-2">{{ $hostel->name }}</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Kamar Asrama</h1><div class="elite-rule"></div></div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1"><div class="bg-white border border-rule p-6 sticky top-6">
<form method="POST" action="{{ route('admin.hostel.rooms.store', $hostel) }}" class="space-y-3">@csrf
<input name="room_no" required maxlength="50" placeholder="No Kamar (101)" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
<input type="number" min="1" max="20" name="capacity" required value="4" placeholder="Kapasitas" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
<input type="number" step="10000" min="0" name="fee_per_month_rupiah" required placeholder="Biaya/bulan (Rp)" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Tambah Kamar</button>
</form></div></div>

<div class="lg:col-span-2"><div class="bg-white border border-rule overflow-hidden">
<table class="w-full text-sm"><thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">No Kamar</th>
<th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Terisi/Kapasitas</th>
<th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Biaya/bulan</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
<th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Aksi</th></tr></thead><tbody>
@forelse($rooms as $r)<tr class="border-t border-rule">
<td class="px-4 py-3 font-mono font-semibold">{{ $r->room_no }}</td>
<td class="px-4 py-3 text-center font-mono">{{ $r->occupied }}/{{ $r->capacity }}</td>
<td class="px-4 py-3 text-right font-mono">Rp {{ number_format($r->fee_per_month/100, 0, ',', '.') }}</td>
<td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded {{ $r->status==='available' ? 'bg-green-100 text-green-700' : ($r->status==='full' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">{{ $r->status }}</span></td>
<td class="px-4 py-3 text-right">
    <a href="{{ route('admin.hostel.beds.index', $r) }}" class="text-xs text-[var(--c-primary)] hover:underline font-semibold">Tempat Tidur</a>
    <form method="POST" action="{{ route('admin.hostel.rooms.destroy', $r) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline ml-2">Hapus</button></form>
</td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada kamar.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection
