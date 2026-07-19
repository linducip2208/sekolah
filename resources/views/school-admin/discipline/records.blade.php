@extends('layouts.school-admin')
@section('title', 'Catatan Disiplin')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.discipline.categories.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kategori</a>

<div class="mb-7"><div class="elite-kicker mb-2">Acta Disciplinae</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Catatan Pelanggaran / Prestasi</h1>
<div class="elite-rule"></div></div>

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Catatan</summary>
<form method="POST" action="{{ route('admin.discipline.records.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-2 gap-3">@csrf
<div><label class="elite-kicker text-[.6rem] block mb-1">Siswa</label>
<select name="student_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— pilih —</option>
@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->user?->name }}</option>@endforeach
</select></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Kategori</label>
<select name="discipline_category_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— pilih —</option>
@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }} ({{ $c->point_value > 0 ? '+' : '' }}{{ $c->point_value }})</option>@endforeach
</select></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Tanggal</label>
<input type="date" name="incident_date" required class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Sanksi/Penghargaan</label>
<input name="sanction_applied" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></div>
<div class="md:col-span-2"><label class="elite-kicker text-[.6rem] block mb-1">Deskripsi</label>
<textarea name="description" rows="3" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea></div>
<div class="md:col-span-2"><button class="btn-elite">Simpan Catatan</button></div>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Kategori</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Poin</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Deskripsi</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Pelapor</th>
</tr></thead><tbody>
@forelse($records as $r)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs">{{ $r->incident_date->format('d M Y') }}</td>
<td class="px-3 py-3 font-serif">{{ $r->student?->user?->name }}</td>
<td class="px-3 py-3 text-xs">{{ $r->category?->name }}</td>
<td class="px-3 py-3 text-right font-mono {{ $r->points < 0 ? 'text-red-700' : 'text-green-700' }}">{{ $r->points > 0 ? '+' : '' }}{{ $r->points }}</td>
<td class="px-3 py-3 text-xs text-gray-700">{{ Str::limit($r->description, 80) }}</td>
<td class="px-3 py-3 text-xs">{{ $r->reporter?->name }}</td>
</tr>@empty<tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada catatan.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $records->links() }}</div>
@endsection
