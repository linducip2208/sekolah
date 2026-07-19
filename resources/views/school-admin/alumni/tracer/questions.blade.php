@extends('layouts.school-admin')
@section('title', 'Pertanyaan Tracer Study')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Alumni</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Pertanyaan Tracer Study</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Kelola pertanyaan untuk tracer study sesuai standar BAN-S/M.</p>
</div>

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Pertanyaan</summary>
<form method="POST" action="{{ route('admin.tracer.questions.store') }}" class="px-5 py-5 border-t border-rule grid gap-3">@csrf
    <textarea name="question_text" required maxlength="500" rows="2" placeholder="Teks pertanyaan..." class="border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
    <select name="question_type" required class="border-2 border-rule px-3 py-2 font-serif text-sm" x-data x-on:change="document.getElementById('optBox').style.display = ['radio','select'].includes($event.target.value) ? '' : 'none'">
        <option value="">— Tipe Pertanyaan —</option>
        <option value="text">Text (jawaban singkat)</option>
        <option value="textarea">Textarea (jawaban panjang)</option>
        <option value="radio">Radio (pilih satu)</option>
        <option value="select">Select (dropdown)</option>
    </select>
    <div id="optBox" style="display:none">
        <textarea name="options" rows="4" placeholder="Opsi (satu per baris)&#10;Opsi A&#10;Opsi B&#10;Opsi C" class="border-2 border-rule px-3 py-2 font-mono text-xs w-full"></textarea>
    </div>
    <div class="flex gap-3 items-center">
        <input type="number" name="sort_order" value="0" min="0" class="border-2 border-rule px-3 py-2 text-sm w-24" placeholder="Urutan">
        <button class="btn-elite">Simpan</button>
    </div>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">#</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Pertanyaan</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tipe</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Aktif</th>
<th></th></tr></thead><tbody>
@forelse($questions as $q)<tr class="border-t border-rule">
<td class="px-3 py-3 font-mono text-xs">{{ $q->sort_order }}</td>
<td class="px-3 py-3 font-serif text-sm">{{ $q->question_text }}</td>
<td class="px-3 py-3 text-xs">{{ $q->question_type }}</td>
<td class="px-3 py-3 text-center">
    <form method="POST" action="{{ route('admin.tracer.questions.update', $q) }}" class="inline">@csrf @method('PUT')
        <input type="hidden" name="question_text" value="{{ $q->question_text }}">
        <input type="hidden" name="question_type" value="{{ $q->question_type }}">
        <input type="hidden" name="sort_order" value="{{ $q->sort_order }}">
        <input type="hidden" name="is_active" value="{{ $q->is_active ? '0' : '1' }}">
        <button class="text-xs {{ $q->is_active ? 'text-green-700' : 'text-red-700' }} underline">{{ $q->is_active ? 'Ya' : 'Tidak' }}</button>
    </form>
</td>
<td class="px-3 py-3 text-right">
    <form method="POST" action="{{ route('admin.tracer.questions.destroy', $q) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')
        <button class="text-xs text-red-700 hover:underline">Hapus</button>
    </form>
</td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada pertanyaan tracer.</td></tr>@endforelse
</tbody></table></div>

<div class="mt-4"><a href="{{ route('admin.tracer.dashboard') }}" class="btn-elite-ghost text-xs">← Kembali ke Dashboard</a></div>
@endsection
