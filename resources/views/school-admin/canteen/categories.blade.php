@extends('layouts.school-admin')
@section('title', 'Kategori Kantin')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7 flex justify-between items-end"><div>
<div class="elite-kicker mb-2">Categoriae Cibariae</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Kategori Kantin</h1><div class="elite-rule"></div></div>
<a href="{{ route('admin.canteen.menu.index') }}" class="btn-elite-ghost">Menu →</a></div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1"><div class="bg-white border border-rule p-6 sticky top-6">
<form method="POST" action="{{ route('admin.canteen.categories.store') }}" class="space-y-3">@csrf
<input name="name" required maxlength="100" placeholder="Makanan/Minuman/Snack" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<input name="icon" maxlength="50" placeholder="Emoji icon (🍱)" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="healthy_tag" value="1"> Tag "Sehat"</label>
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Tambah</button>
</form></div></div>

<div class="lg:col-span-2"><div class="bg-white border border-rule overflow-hidden">
<table class="w-full text-sm"><thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Icon</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Healthy</th>
<th></th></tr></thead><tbody>
@forelse($categories as $c)<tr class="border-t border-rule">
<td class="px-4 py-3">{{ $c->icon ?? '—' }}</td>
<td class="px-4 py-3 font-serif font-semibold">{{ $c->name }}</td>
<td class="px-4 py-3">@if($c->healthy_tag)<span class="text-xs text-green-700">✓</span>@else<span class="text-xs text-gray-400">—</span>@endif</td>
<td class="px-4 py-3 text-right"><form method="POST" action="{{ route('admin.canteen.categories.destroy', $c) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form></td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada kategori.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection
