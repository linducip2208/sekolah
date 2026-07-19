@extends('layouts.school-admin')
@section('title', 'Program Beasiswa')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7 flex justify-between items-end"><div>
<div class="elite-kicker mb-2">Programmae Subsidiorum</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Program Beasiswa</h1><div class="elite-rule"></div></div>
<a href="{{ route('admin.scholarship.applications.index') }}" class="btn-elite-ghost">Pendaftar →</a></div>

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Buat Program</summary>
<form method="POST" action="{{ route('admin.scholarship.programs.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-3 gap-3">@csrf
<input name="name" required maxlength="200" placeholder="Beasiswa Prestasi 2026" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
<select name="source" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="school">Sekolah</option><option value="foundation">Yayasan</option>
<option value="government">Pemerintah</option><option value="corporate">Korporasi</option>
<option value="individual">Individu</option>
</select>
<select name="discount_type" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="percentage">Persentase (%)</option>
<option value="fixed">Nominal Tetap (Rp)</option>
<option value="full">Bebas SPP 100%</option>
</select>
<input type="number" step="0.01" min="0" name="discount_value" required placeholder="Nilai diskon" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<input type="number" min="1" name="quota" placeholder="Kuota" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<input type="date" name="open_date" required class="border-2 border-rule px-3 py-2 text-sm">
<input type="date" name="close_date" required class="border-2 border-rule px-3 py-2 text-sm">
<div class="md:col-span-3"><button class="btn-elite">Simpan</button></div>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Nama</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Sumber</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Diskon</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Periode</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Kuota</th>
<th></th></tr></thead><tbody>
@forelse($programs as $p)<tr class="border-t border-rule">
<td class="px-3 py-3 font-serif font-semibold">{{ $p->name }}</td>
<td class="px-3 py-3"><span class="elite-kicker text-[.55rem]">{{ $p->source }}</span></td>
<td class="px-3 py-3 text-xs">@if($p->discount_type==='percentage'){{ $p->discount_value }}%
@elseif($p->discount_type==='fixed') Rp {{ number_format($p->discount_value/100,0,',','.') }}
@else Bebas Penuh @endif</td>
<td class="px-3 py-3 text-xs">{{ $p->open_date?->format('d M') }}–{{ $p->close_date?->format('d M Y') }}</td>
<td class="px-3 py-3 text-center font-mono">{{ $p->quota ?? '∞' }}</td>
<td class="px-3 py-3 text-right"><form method="POST" action="{{ route('admin.scholarship.programs.destroy', $p) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form></td>
</tr>@empty<tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada program.</td></tr>@endforelse
</tbody></table></div>
@endsection
