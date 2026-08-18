@extends('layouts.school-admin')
@section('title', 'Menu Mess Asrama')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Asrama</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Menu Mess</h1><div class="elite-rule"></div></div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1"><div class="bg-white border border-rule p-6 sticky top-6">
<form method="POST" action="{{ route('admin.hostel.mess-menus.store') }}" class="space-y-3">@csrf
<select name="hostel_id" required class="w-full border-2 border-rule px-3 py-2 text-sm"><option value="">— Asrama —</option>
@foreach($hostels as $h)<option value="{{ $h->id }}">{{ $h->name }}</option>@endforeach</select>
<select name="day_of_week" required class="w-full border-2 border-rule px-3 py-2 text-sm">
<option value="0">Minggu</option><option value="1">Senin</option><option value="2">Selasa</option><option value="3">Rabu</option>
<option value="4">Kamis</option><option value="5">Jumat</option><option value="6">Sabtu</option></select>
<select name="meal_type" required class="w-full border-2 border-rule px-3 py-2 text-sm">
<option value="breakfast">Sarapan</option><option value="lunch">Makan Siang</option><option value="dinner">Makan Malam</option></select>
<textarea name="menu_description" required rows="3" maxlength="1000" placeholder="Deskripsi menu..." class="w-full border-2 border-rule px-3 py-2 text-sm"></textarea>
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan Menu</button>
</form></div></div>

<div class="lg:col-span-2"><div class="bg-white border border-rule overflow-hidden">
<table class="w-full text-sm"><thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Asrama</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Hari</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Waktu Makan</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Menu</th>
<th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Aksi</th>
</tr></thead><tbody>
@php $days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu']; $meals = ['breakfast'=>'Sarapan','lunch'=>'Siang','dinner'=>'Malam']; @endphp
@forelse($menus as $m)<tr class="border-t border-rule">
<td class="px-4 py-3 text-sm font-medium">{{ $m->hostel->name }}</td>
<td class="px-4 py-3 text-sm">{{ $days[$m->day_of_week] }}</td>
<td class="px-4 py-3 text-sm">{{ $meals[$m->meal_type] }}</td>
<td class="px-4 py-3 text-sm max-w-[300px] truncate">{{ $m->menu_description }}</td>
<td class="px-4 py-3 text-right">
    <form method="POST" action="{{ route('admin.hostel.mess-menus.destroy', $m) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')
        <button class="text-xs text-red-700 hover:underline">Hapus</button></form>
</td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada menu.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection
