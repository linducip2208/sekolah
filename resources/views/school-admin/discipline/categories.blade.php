@extends('layouts.school-admin')
@section('title', 'Kategori Disiplin')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7 flex justify-between items-end"><div>
<div class="elite-kicker mb-2">Categoriae Disciplinae</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Kategori Disiplin</h1>
<div class="elite-rule"></div></div>
<a href="{{ route('admin.discipline.records.index') }}" class="btn-elite-ghost">Catatan Pelanggaran →</a></div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1"><div class="bg-white border border-rule p-6 sticky top-6">
<h3 class="elite-h3 text-base ink-primary mb-3">Tambah Kategori</h3>
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('admin.discipline.categories.store') }}" class="space-y-3">@csrf
<input name="name" required maxlength="200" placeholder="Terlambat / Bolos / Juara Olimpiade" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<select name="type" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="violation">Pelanggaran (–)</option>
<option value="achievement">Prestasi (+)</option>
</select>
<input type="number" name="point_value" required placeholder="Poin (mis. -10 atau +20)" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
<textarea name="description" rows="2" placeholder="Deskripsi" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan</button>
</form></div></div>

<div class="lg:col-span-2"><div class="bg-white border border-rule overflow-hidden">
<table class="w-full text-sm"><thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tipe</th>
<th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Poin</th>
<th></th></tr></thead><tbody>
@forelse($categories as $c)<tr class="border-t border-rule">
<td class="px-4 py-3 font-serif font-semibold">{{ $c->name }}</td>
<td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded {{ $c->type==='achievement' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $c->type }}</span></td>
<td class="px-4 py-3 text-right font-mono {{ $c->point_value < 0 ? 'text-red-700' : 'text-green-700' }}">{{ $c->point_value > 0 ? '+' : '' }}{{ $c->point_value }}</td>
<td class="px-4 py-3 text-right"><form method="POST" action="{{ route('admin.discipline.categories.destroy', $c) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form></td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada kategori.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection
