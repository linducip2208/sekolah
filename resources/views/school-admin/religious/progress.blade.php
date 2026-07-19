@extends('layouts.school-admin')
@section('title', 'Progress Hafalan')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<a href="{{ route('admin.religious.targets.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Target Hafalan</a>
<div class="mb-7"><div class="elite-kicker mb-2">Progressus Hafalan</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Catatan Setoran Hafalan</h1><div class="elite-rule"></div></div>

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Catat Setoran</summary>
<form method="POST" action="{{ route('admin.religious.progress.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-3 gap-3">@csrf
<select name="student_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— siswa —</option>
@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->user?->name }}</option>@endforeach
</select>
<select name="hafalan_target_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— target —</option>
@foreach($targets as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
</select>
<input type="date" name="memorized_at" required value="{{ now()->toDateString() }}" class="border-2 border-rule px-3 py-2 text-sm">
<input name="surah" required maxlength="100" placeholder="Surah" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<input type="number" name="ayah_start" min="1" required placeholder="Ayat awal" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<input type="number" name="ayah_end" min="1" required placeholder="Ayat akhir" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<select name="quality" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="excellent">Excellent</option><option value="good">Good</option>
<option value="fair">Fair</option><option value="needs_review">Perlu Review</option>
</select>
<input name="note" placeholder="Catatan" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
<div class="md:col-span-3"><button class="btn-elite">Simpan Setoran</button></div>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tgl</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Target</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Surah:Ayat</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Kualitas</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Penguji</th>
</tr></thead><tbody>
@forelse($progresses as $p)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs">{{ $p->memorized_at?->format('d M Y') }}</td>
<td class="px-3 py-3 font-serif">{{ $p->student?->user?->name }}</td>
<td class="px-3 py-3 text-xs">{{ $p->target?->name }}</td>
<td class="px-3 py-3 font-mono text-xs">{{ $p->surah }} {{ $p->ayah_start }}-{{ $p->ayah_end }}</td>
<td class="px-3 py-3"><span class="elite-kicker text-[.55rem]">{{ $p->quality }}</span></td>
<td class="px-3 py-3 text-xs">{{ $p->verifier?->name }}</td>
</tr>@empty<tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada setoran.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $progresses->links() }}</div>
@endsection
