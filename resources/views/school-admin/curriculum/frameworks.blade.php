@extends('layouts.school-admin')
@section('title', 'Kurikulum')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Curriculum</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Framework Kurikulum</h1><div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">Mis. Merdeka, K13, IB, Cambridge, Diniyah.</p></div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1"><div class="bg-white border border-rule p-6 sticky top-6">
<form method="POST" action="{{ route('admin.curriculum.frameworks.store') }}" class="space-y-3">@csrf
<input name="name" required maxlength="200" placeholder="Nama framework" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<select name="type" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="merdeka">Merdeka</option><option value="k13">K13</option>
<option value="cambridge">Cambridge</option><option value="ib">IB</option>
<option value="montessori">Montessori</option><option value="diniyah">Diniyah</option>
<option value="custom">Custom</option>
</select>
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan</button>
</form></div></div>

<div class="lg:col-span-2"><div class="bg-white border border-rule overflow-hidden">
<table class="w-full text-sm"><thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tipe</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
<th></th></tr></thead><tbody>
@forelse($frameworks as $f)<tr class="border-t border-rule">
<td class="px-4 py-3 font-serif font-semibold">{{ $f->name }}</td>
<td class="px-4 py-3"><span class="elite-kicker text-[.55rem]">{{ $f->type }}</span></td>
<td class="px-4 py-3"><span class="text-xs {{ $f->is_active ? 'text-green-700' : 'text-gray-500' }}">{{ $f->is_active ? '● Aktif' : 'Off' }}</span></td>
<td class="px-4 py-3 text-right"><form method="POST" action="{{ route('admin.curriculum.frameworks.destroy', $f) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form></td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada framework.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection
