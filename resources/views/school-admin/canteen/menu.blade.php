@extends('layouts.school-admin')
@section('title', 'Menu Kantin')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<a href="{{ route('admin.canteen.categories.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kategori</a>

<div class="mb-7"><div class="elite-kicker mb-2">Menu Cibariae</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Menu Kantin</h1><div class="elite-rule"></div></div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1"><div class="bg-white border border-rule p-6 sticky top-6">
<h3 class="elite-h3 text-base ink-primary mb-3">Tambah Menu</h3>
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('admin.canteen.menu.store') }}" class="space-y-3">@csrf
<input name="name" required maxlength="200" placeholder="Nasi Goreng" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<select name="canteen_category_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— kategori —</option>
@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
</select>
<input type="number" step="500" min="0" name="price_rupiah" required placeholder="Harga (Rp)" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
<textarea name="description" rows="2" placeholder="Deskripsi" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Tambah Menu</button>
</form></div></div>

<div class="lg:col-span-2"><div class="bg-white border border-rule overflow-hidden">
<table class="w-full text-sm"><thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Nama</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Kategori</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Harga</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Stok</th>
<th></th></tr></thead><tbody>
@forelse($items as $i)<tr class="border-t border-rule">
<td class="px-3 py-3 font-serif font-semibold">{{ $i->name }}</td>
<td class="px-3 py-3 text-xs">{{ $i->category?->name ?? '—' }}</td>
<td class="px-3 py-3 text-right font-mono">Rp {{ number_format($i->price/100, 0, ',', '.') }}</td>
<td class="px-3 py-3 text-center text-xs">{{ $i->stock_today ?? '∞' }}</td>
<td class="px-3 py-3 text-right"><form method="POST" action="{{ route('admin.canteen.menu.destroy', $i) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form></td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada menu.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection
