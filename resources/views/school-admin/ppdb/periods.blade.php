@extends('layouts.school-admin')
@section('title', 'Periode PPDB')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7"><div class="elite-kicker mb-2">Tempora PPDB</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Periode PPDB</h1>
<div class="elite-rule"></div></div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1"><div class="bg-white border border-rule p-6 sticky top-6">
<h3 class="elite-h3 text-base ink-primary mb-3">Tambah Periode</h3>
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('admin.ppdb.periods.store') }}" class="space-y-3">@csrf
<input name="name" required maxlength="200" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="PPDB 2026/2027">
<select name="academic_year_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— Tahun Ajaran —</option>
@foreach($years as $y)<option value="{{ $y->id }}">{{ $y->name }}</option>@endforeach
</select>
<div><label class="elite-kicker text-[.6rem]">Buka</label><input type="date" name="open_date" required class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
<div><label class="elite-kicker text-[.6rem]">Tutup</label><input type="date" name="close_date" required class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
<div><label class="elite-kicker text-[.6rem]">Pengumuman</label><input type="date" name="announcement_date" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
<div><label class="elite-kicker text-[.6rem]">Daftar Ulang</label><input type="date" name="reregistration_deadline" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
<div><label class="elite-kicker text-[.6rem]">Biaya Formulir (Rp)</label><input type="number" min="0" name="form_fee_rupiah" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm"></div>
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan</button>
</form></div></div>

<div class="lg:col-span-2"><div class="bg-white border border-rule overflow-hidden">
<table class="w-full text-sm"><thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Periode</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
<th></th></tr></thead><tbody>
@forelse($periods as $p)<tr class="border-t border-rule">
<td class="px-4 py-3 font-serif font-semibold ink-primary">{{ $p->name }}</td>
<td class="px-4 py-3 text-xs">{{ $p->open_date->format('d M Y') }} → {{ $p->close_date->format('d M Y') }}</td>
<td class="px-4 py-3">@if($p->is_published)<span class="text-xs text-green-700">● Published</span>@else<span class="text-xs text-gray-500">Draft</span>@endif</td>
<td class="px-4 py-3 text-right whitespace-nowrap">
<form method="POST" action="{{ route('admin.ppdb.periods.publish', $p) }}" class="inline">@csrf<button class="text-xs underline ink-secondary hover:ink-accent">{{ $p->is_published ? 'Unpublish' : 'Publish' }}</button></form>
<form method="POST" action="{{ route('admin.ppdb.periods.destroy', $p) }}" class="inline ml-2" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
</td></tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada periode.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection
