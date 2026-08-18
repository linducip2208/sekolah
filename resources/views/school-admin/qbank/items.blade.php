@extends('layouts.school-admin')
@section('title', 'Bank Soal')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<a href="{{ route('admin.qbank.categories.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">&larr; Kategori</a>
<div class="mb-7"><div class="elite-kicker mb-2">Quaestiones</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Bank Soal</h1><div class="elite-rule"></div></div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-sm text-green-800">{{ session('success') }}</div>
@endif

<div x-data="{ tab: 'items' }" class="space-y-6">
<div class="flex flex-wrap gap-2 border-b border-rule pb-3">
    <button @click="tab = 'items'" :class="tab === 'items' ? 'bg-[var(--c-primary)] text-white' : 'bg-white text-gray-700 hover:bg-gray-50'" class="px-4 py-2 text-sm font-serif border border-rule transition-colors">Daftar Soal</button>
    <button @click="tab = 'tags'" :class="tab === 'tags' ? 'bg-[var(--c-primary)] text-white' : 'bg-white text-gray-700 hover:bg-gray-50'" class="px-4 py-2 text-sm font-serif border border-rule transition-colors">Tags</button>
    <button @click="tab = 'blueprints'" :class="tab === 'blueprints' ? 'bg-[var(--c-primary)] text-white' : 'bg-white text-gray-700 hover:bg-gray-50'" class="px-4 py-2 text-sm font-serif border border-rule transition-colors">Blueprint</button>
    <button @click="tab = 'review'" :class="tab === 'review' ? 'bg-[var(--c-primary)] text-white' : 'bg-white text-gray-700 hover:bg-gray-50'" class="px-4 py-2 text-sm font-serif border border-rule transition-colors">Review</button>
</div>

<!-- ITEMS TAB -->
<div x-show="tab === 'items'">
<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Soal</summary>
<form method="POST" action="{{ route('admin.qbank.items.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-3 gap-3">@csrf
<select name="subject_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">-- mapel --</option>
@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</select>
<select name="question_bank_category_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">-- kategori --</option>
@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
</select>
<select name="type" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="multiple_choice">Pilihan Ganda</option>
<option value="true_false">Benar/Salah</option>
<option value="essay">Essay</option>
<option value="matching">Menjodohkan</option>
<option value="fill_blank">Isian</option>
</select>
<select name="difficulty" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="medium">Sedang</option>
<option value="easy">Mudah</option>
<option value="hard">Sulit</option>
</select>
<select name="cognitive_level" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">-- level kognitif --</option>
<option value="c4">Analisis (C4)</option>
<option value="c5">Evaluasi (C5)</option>
<option value="c6">Creating (C6)</option>
<option value="c7">C7</option>
<option value="c8">C8</option>
</select>
<div class="flex items-center gap-2">
<input type="checkbox" name="is_published" value="1" checked id="q-published" class="w-4 h-4">
<label for="q-published" class="text-sm font-serif">Publish (dipakai generator)</label>
</div>
<textarea name="question_html" rows="3" required maxlength="10000" placeholder="Soal" class="md:col-span-3 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
<textarea name="options_text" rows="4" placeholder="Pilihan jawaban (Pilihan Ganda) -- satu per baris, awali * untuk kunci" class="md:col-span-3 border-2 border-rule px-3 py-2 font-mono text-xs"></textarea>
<input name="answer_key" placeholder="Kunci jawaban (Benar/Salah, Isian, Essay)" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
<input name="tags" placeholder="Tag (koma): HOTS, AKM, semester-1" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<textarea name="explanation_html" rows="2" placeholder="Pembahasan" class="md:col-span-3 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
<div class="md:col-span-3"><button class="btn-elite">Simpan Soal</button></div>
</form></details>

<div class="bg-white border border-rule overflow-x-auto"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Soal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Mapel</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Kategori</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tipe</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tingkat</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Kognitif</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Status</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Ver</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Analisis</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Pakai</th>
<th></th></tr></thead><tbody>
@forelse($items as $i)<tr class="border-t border-rule">
<td class="px-3 py-3 font-serif">{{ Str::limit(strip_tags($i->question_html), 60) }}</td>
<td class="px-3 py-3 text-xs">{{ $i->subject?->name }}</td>
<td class="px-3 py-3 text-xs">{{ $i->category?->name }}</td>
<td class="px-3 py-3"><span class="elite-kicker text-[.55rem]">{{ $i->question_type ?? $i->type }}</span></td>
<td class="px-3 py-3"><span class="text-xs @if($i->difficulty==='easy') text-green-700 @elseif($i->difficulty==='hard') text-red-700 @else text-amber-700 @endif font-semibold">{{ $i->difficulty ?? '—' }}</span></td>
<td class="px-3 py-3 text-xs">{{ strtoupper($i->cognitive_level ?? '—') }}</td>
<td class="px-3 py-3 text-center">
    @if($i->status === 'approved')<span class="text-xs text-green-700 font-semibold">Approved</span>
    @elseif($i->status === 'submitted')<span class="text-xs text-blue-600">Submitted</span>
    @elseif($i->status === 'rejected')<span class="text-xs text-red-600">Rejected</span>
    @else<span class="text-xs text-gray-400">Draft</span>@endif
</td>
<td class="px-3 py-3 text-center font-mono text-xs">v{{ $i->version ?? 1 }}</td>
<td class="px-3 py-3 text-center text-xs">
    @if($i->total_attempts > 0)
        <span class="text-gray-600">{{ number_format($i->avg_score_pct ?? 0, 0) }}%</span>
    @else<span class="text-gray-300">--</span>@endif
</td>
<td class="px-3 py-3 text-center font-mono text-xs">{{ $i->used_count }}</td>
<td class="px-3 py-3 text-right space-x-2">
    <a href="{{ route('admin.qbank.variasi.form', $i) }}" class="text-xs text-blue-600 hover:underline">Variasi AI</a>
    <form method="POST" action="{{ route('admin.qbank.items.destroy', $i) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
</td>
</tr>@empty<tr><td colspan="11" class="p-10 text-center text-gray-500 italic font-serif">Belum ada soal.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $items->links() }}</div>
</div>

<!-- TAGS TAB -->
<div x-show="tab === 'tags'" x-cloak class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1"><div class="bg-white border border-rule p-6 sticky top-6">
<h3 class="elite-h3 text-base ink-primary mb-3">Tambah Tag</h3>
<form method="POST" action="{{ route('admin.qbank.tags.store') }}" class="space-y-3">@csrf
<input name="name" required maxlength="100" placeholder="Nama tag" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<input name="color" type="color" value="#6366f1" class="w-full h-10 border-2 border-rule cursor-pointer">
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan</button>
</form></div></div>
<div class="lg:col-span-2"><div class="bg-white border border-rule overflow-hidden">
<table class="w-full text-sm"><thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
<th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Warna</th>
<th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Jumlah Soal</th>
<th></th></tr></thead><tbody>
@forelse($tags as $t)<tr class="border-t border-rule">
<td class="px-4 py-3 font-serif font-semibold">{{ $t->name }}</td>
<td class="px-4 py-3 text-center"><span class="inline-block w-5 h-5 rounded" style="background:{{ $t->color ?? '#6366f1' }}"></span></td>
<td class="px-4 py-3 text-center font-mono text-xs">{{ $t->items_count ?? $t->items()->count() }}</td>
<td class="px-4 py-3 text-right"><form method="POST" action="{{ route('admin.qbank.tags.destroy', $t) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form></td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada tag.</td></tr>@endforelse
</tbody></table></div></div></div>

<!-- BLUEPRINTS TAB -->
<div x-show="tab === 'blueprints'" x-cloak class="space-y-6">
<div class="bg-white border border-rule p-6">
<h3 class="elite-kicker text-[.65rem] mb-3">Buat Blueprint Soal</h3>
<form method="POST" action="{{ route('admin.qbank.blueprints.store') }}" class="grid md:grid-cols-4 gap-3 items-end">@csrf
<div>
<label class="text-xs text-gray-500 mb-1 block">Nama Blueprint</label>
<input name="name" required maxlength="200" placeholder="UTS Matematika VII" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
</div>
<div>
<label class="text-xs text-gray-500 mb-1 block">Jumlah Soal</label>
<input name="total_items" type="number" min="1" max="200" value="20" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
</div>
<div>
<label class="text-xs text-gray-500 mb-1 block">Distribusi (JSON)</label>
<input name="distribution" placeholder='{"easy":5,"medium":10,"hard":5}' class="w-full border-2 border-rule px-3 py-2 font-mono text-xs">
</div>
<div><button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan Blueprint</button></div>
</form></div>

<div class="bg-white border border-rule overflow-hidden">
<table class="w-full text-sm"><thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
<th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Total Soal</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Distribusi</th>
<th></th></tr></thead><tbody>
@forelse($blueprints as $bp)<tr class="border-t border-rule">
<td class="px-4 py-3 font-serif font-semibold">{{ $bp->name }}</td>
<td class="px-4 py-3 text-center font-mono">{{ $bp->total_items }}</td>
<td class="px-4 py-3 text-xs font-mono text-gray-600">{{ json_encode($bp->distribution) }}</td>
<td class="px-4 py-3 text-right"><form method="POST" action="{{ route('admin.qbank.blueprints.destroy', $bp) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form></td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada blueprint.</td></tr>@endforelse
</tbody></table></div>
</div>

<!-- REVIEW TAB -->
<div x-show="tab === 'review'" x-cloak>
<div class="bg-white border border-rule overflow-x-auto">
<table class="w-full text-sm"><thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Soal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Mapel</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Dibuat Oleh</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Reviewed Oleh</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Waktu Review</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Aksi</th>
</tr></thead><tbody>
@forelse($reviewItems as $i)<tr class="border-t border-rule">
<td class="px-3 py-3 font-serif">{{ Str::limit(strip_tags($i->question_html), 50) }}</td>
<td class="px-3 py-3 text-xs">{{ $i->subject?->name }}</td>
<td class="px-3 py-3">
    @if($i->status === 'approved')<span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded font-semibold">Approved</span>
    @elseif($i->status === 'submitted')<span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded">Submitted</span>
    @elseif($i->status === 'rejected')<span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded">Rejected</span>
    @else<span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded">Draft</span>@endif
</td>
<td class="px-3 py-3 text-xs">{{ $i->author?->name ?? '—' }}</td>
<td class="px-3 py-3 text-xs">{{ $i->reviewer?->name ?? '—' }}</td>
<td class="px-3 py-3 text-xs">{{ $i->reviewed_at?->format('d M Y H:i') ?? '—' }}</td>
<td class="px-3 py-3 text-center space-x-2">
    <form method="POST" action="{{ route('admin.qbank.review.action', $i) }}" class="inline">@csrf
        <input type="hidden" name="action" value="approved">
        <button class="text-xs text-green-700 hover:underline font-semibold">Approve</button>
    </form>
    <form method="POST" action="{{ route('admin.qbank.review.action', $i) }}" class="inline">@csrf
        <input type="hidden" name="action" value="rejected">
        <button class="text-xs text-red-700 hover:underline">Reject</button>
    </form>
</td>
</tr>@empty<tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Tidak ada soal untuk review.</td></tr>@endforelse
</tbody></table></div>
</div>

</div>
@endsection
