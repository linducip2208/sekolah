@extends('layouts.school-admin')
@section('title', 'Bank Soal')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<a href="{{ route('admin.qbank.categories.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kategori</a>
<div class="mb-7"><div class="elite-kicker mb-2">Quaestiones</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Bank Soal</h1><div class="elite-rule"></div></div>

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Soal</summary>
<form method="POST" action="{{ route('admin.qbank.items.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-3 gap-3">@csrf
<select name="subject_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— mapel —</option>
@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</select>
<select name="question_bank_category_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— kategori —</option>
@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
</select>
<select name="type" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="multiple_choice">Pilihan Ganda</option>
<option value="true_false">Benar/Salah</option>
<option value="short_answer">Isian Singkat</option>
<option value="essay">Essay</option>
</select>
<select name="difficulty" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="medium">Sedang</option>
<option value="easy">Mudah</option>
<option value="hard">Sulit</option>
</select>
<select name="cognitive_level" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— level kognitif —</option>
<option value="remembering">Mengingat (C1)</option>
<option value="understanding">Memahami (C2)</option>
<option value="applying">Menerapkan (C3)</option>
<option value="analyzing">Menganalisis (C4)</option>
<option value="evaluating">Mengevaluasi (C5)</option>
<option value="creating">Mencipta (C6)</option>
</select>
<div class="flex items-center gap-2">
<input type="checkbox" name="is_published" value="1" checked id="q-published" class="w-4 h-4">
<label for="q-published" class="text-sm font-serif">Publish (dipakai generator)</label>
</div>
<textarea name="question_html" rows="3" required maxlength="10000" placeholder="Soal" class="md:col-span-3 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
<textarea name="options_text" rows="4" placeholder="Pilihan jawaban (Pilihan Ganda) — satu per baris, awali * untuk kunci" class="md:col-span-3 border-2 border-rule px-3 py-2 font-mono text-xs"></textarea>
<input name="answer_key" placeholder="Kunci jawaban (Benar/Salah, Isian, Essay)" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
<input name="tags" placeholder="Tag (koma): HOTS, AKM, semester-1" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<textarea name="explanation_html" rows="2" placeholder="Pembahasan" class="md:col-span-3 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
<div class="md:col-span-3"><button class="btn-elite">Simpan Soal</button></div>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Soal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Mapel</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Kategori</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tipe</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tingkat</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Kognitif</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Status</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Pakai</th>
<th></th></tr></thead><tbody>
@forelse($items as $i)<tr class="border-t border-rule">
<td class="px-3 py-3 font-serif">{{ Str::limit(strip_tags($i->question_html), 60) }}</td>
<td class="px-3 py-3 text-xs">{{ $i->subject?->name }}</td>
<td class="px-3 py-3 text-xs">{{ $i->category?->name }}</td>
<td class="px-3 py-3"><span class="elite-kicker text-[.55rem]">{{ $i->type }}</span></td>
<td class="px-3 py-3"><span class="text-xs @if($i->difficulty==='easy') text-green-700 @elseif($i->difficulty==='hard') text-red-700 @else text-amber-700 @endif">{{ $i->difficulty ?? '—' }}</span></td>
<td class="px-3 py-3 text-xs">{{ $i->cognitive_level ?? '—' }}</td>
<td class="px-3 py-3 text-center">@if($i->is_published)<span class="text-xs text-green-700">✓ Publish</span>@else<span class="text-xs text-gray-400">Draft</span>@endif</td>
<td class="px-3 py-3 text-center font-mono text-xs">{{ $i->used_count }}</td>
<td class="px-3 py-3 text-right"><form method="POST" action="{{ route('admin.qbank.items.destroy', $i) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form></td>
</tr>@empty<tr><td colspan="9" class="p-10 text-center text-gray-500 italic font-serif">Belum ada soal.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
