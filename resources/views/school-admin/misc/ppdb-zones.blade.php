@extends('layouts.school-admin')
@section('title', 'Zona PPDB')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><h1 class="elite-h1 text-3xl ink-primary mb-2">Zona PPDB Zonasi</h1><div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">Setup district/kelurahan untuk priority score jalur zonasi PPDB.</p></div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1"><div class="bg-white border border-rule p-6 sticky top-6">
<form method="POST" action="{{ route('admin.misc.ppdb-zones.store') }}" class="space-y-3">@csrf
<input name="district" required maxlength="200" placeholder="Kecamatan" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<input name="subdistrict" maxlength="200" placeholder="Kelurahan (opsional)" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<input type="number" step="1" min="0" max="1000" name="priority_score" required placeholder="Priority score (0-1000)" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Tambah Zona</button>
</form></div></div>

<div class="lg:col-span-2"><div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kecamatan</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kelurahan</th>
<th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Priority Score</th>
</tr></thead><tbody>
@forelse($zones as $z)<tr class="border-t border-rule">
<td class="px-4 py-3 font-serif font-semibold">{{ $z->district }}</td>
<td class="px-4 py-3 text-xs">{{ $z->subdistrict ?? '—' }}</td>
<td class="px-4 py-3 text-right font-mono">{{ $z->priority_score }}</td>
</tr>@empty<tr><td colspan="3" class="p-10 text-center text-gray-500 italic font-serif">Belum ada zona.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection
