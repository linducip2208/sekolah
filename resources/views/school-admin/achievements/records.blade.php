@extends('layouts.school-admin')
@section('title', 'Prestasi Siswa')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<a href="{{ route('admin.achievements.categories.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kategori</a>
<div class="mb-7"><div class="elite-kicker mb-2">Honoris Discipulorum</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Catatan Prestasi Siswa</h1><div class="elite-rule"></div></div>

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Catat Prestasi</summary>
<form method="POST" action="{{ route('admin.achievements.records.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-2 gap-3">@csrf
<select name="student_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— siswa —</option>
@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->user?->name }}</option>@endforeach
</select>
<select name="achievement_category_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— kategori —</option>
@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }} ({{ $c->scope }})</option>@endforeach
</select>
<input name="title" required maxlength="255" placeholder="Judul prestasi" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
<input type="date" name="achieved_at" required class="border-2 border-rule px-3 py-2 text-sm">
<input name="issuer" placeholder="Penerbit/Penyelenggara" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<textarea name="description" rows="2" placeholder="Deskripsi" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
<div class="md:col-span-2"><button class="btn-elite">Simpan</button></div>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tgl</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Prestasi</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Skala</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Verified</th>
</tr></thead><tbody>
@forelse($achievements as $a)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs">{{ $a->achieved_at?->format('d M Y') }}</td>
<td class="px-3 py-3 font-serif">{{ $a->student?->user?->name }}</td>
<td class="px-3 py-3"><div class="font-serif font-semibold">{{ $a->title }}</div><div class="text-xs text-gray-500">{{ $a->issuer ?? '—' }}</div></td>
<td class="px-3 py-3 text-xs">{{ $a->category?->scope ?? '—' }}</td>
<td class="px-3 py-3">@if($a->verified)<span class="text-xs text-green-700">✓</span>@else<span class="text-xs text-gray-400">—</span>@endif</td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada prestasi.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $achievements->links() }}</div>
@endsection
