@extends('layouts.school-admin')
@section('title', 'Daftar Aset')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7 flex justify-between items-end"><div>
<div class="elite-kicker mb-2">Bona</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Daftar Aset</h1><div class="elite-rule"></div></div>
<div class="flex gap-2">
<a href="{{ route('admin.inventory.categories.index') }}" class="btn-elite-ghost">Kategori</a>
<a href="{{ route('admin.inventory.loans.index') }}" class="btn-elite-ghost">Peminjaman</a>
</div></div>

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Aset</summary>
<form method="POST" action="{{ route('admin.inventory.assets.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-3 gap-3">@csrf
<input name="asset_code" required maxlength="50" placeholder="Kode aset (AST-001)" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<input name="name" required maxlength="200" placeholder="Nama aset" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
<select name="asset_category_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— kategori —</option>
@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
</select>
<input name="serial_number" maxlength="100" placeholder="Serial number" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<input name="location" maxlength="200" placeholder="Lokasi" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<select name="condition" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="excellent">Sangat Baik</option><option value="good">Baik</option>
<option value="fair">Cukup</option><option value="poor">Buruk</option><option value="damaged">Rusak</option>
</select>
<input type="date" name="purchased_at" class="border-2 border-rule px-3 py-2 text-sm">
<input type="number" step="1000" min="0" name="purchase_price_rupiah" placeholder="Harga beli (Rp)" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<div class="md:col-span-3"><button class="btn-elite">Simpan Aset</button></div>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Kode</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Nama</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Kategori</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Lokasi</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Kondisi</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
<th></th></tr></thead><tbody>
@forelse($assets as $a)<tr class="border-t border-rule">
<td class="px-3 py-3 font-mono text-xs">{{ $a->asset_code }}</td>
<td class="px-3 py-3 font-serif font-semibold">{{ $a->name }}</td>
<td class="px-3 py-3 text-xs">{{ $a->category?->name ?? '—' }}</td>
<td class="px-3 py-3 text-xs">{{ $a->location ?? '—' }}</td>
<td class="px-3 py-3"><span class="elite-kicker text-[.55rem]">{{ $a->condition }}</span></td>
<td class="px-3 py-3"><span class="text-xs px-2 py-0.5 rounded {{ $a->status==='available' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $a->status }}</span></td>
<td class="px-3 py-3 text-right"><form method="POST" action="{{ route('admin.inventory.assets.destroy', $a) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form></td>
</tr>@empty<tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada aset.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $assets->links() }}</div>
@endsection
