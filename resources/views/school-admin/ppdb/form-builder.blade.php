@extends('layouts.school-admin')
@section('title', 'Form Builder PPDB')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7"><div class="elite-kicker mb-2">Formulir PPDB</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Form Builder</h1>
<div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">Konfigurasi field formulir pendaftaran PPDB per periode.</p></div>

<form method="GET" class="bg-white border border-rule p-4 mb-6 flex gap-3 items-end flex-wrap">
<div>
    <label class="elite-kicker text-[.6rem] block mb-1">Periode</label>
    <select name="period_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">— Pilih Periode —</option>
        @foreach($periods as $p)<option value="{{ $p->id }}" @selected($periodId == $p->id)>{{ $p->name }}</option>@endforeach
    </select>
</div>
<button class="btn-elite" style="padding:.6rem 1rem;font-size:.65rem;">Tampilkan</button>
</form>

@if($periodId)
<div class="grid lg:grid-cols-3 gap-6">
    {{-- Add field form --}}
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Tambah Field</h3>
            @if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('admin.ppdb.form-builder.store') }}" class="space-y-3" x-data="{ type: 'text' }">
                @csrf
                <input type="hidden" name="period_id" value="{{ $periodId }}">
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Nama Field</label>
                    <input name="field_name" required maxlength="100" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" placeholder="e.g. nama_ayah">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Label</label>
                    <input name="field_label" required maxlength="200" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="e.g. Nama Ayah">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tipe</label>
                    <select name="field_type" x-model="type" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        @foreach(['text','textarea','number','date','file','select','checkbox','radio'] as $t)
                        <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="['select','checkbox','radio'].includes(type)" x-cloak>
                    <label class="elite-kicker text-[.6rem] block mb-1">Opsi (satu per baris)</label>
                    <textarea name="options[]" rows="4" class="w-full border-2 border-rule px-3 py-2 font-mono text-xs" placeholder="Opsi 1&#10;Opsi 2&#10;Opsi 3"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_required" value="1" checked class="rounded">
                    <label class="text-xs font-serif">Wajib diisi</label>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Urutan</label>
                    <input type="number" name="sort_order" min="0" value="{{ $fields->max('sort_order') + 1 ?? 1 }}" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
                </div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Tambah Field</button>
            </form>
        </div>
    </div>

    {{-- Fields list --}}
    <div class="lg:col-span-2">
        <div class="bg-white border border-rule overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white"><tr>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Urutan</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Label</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tipe</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Wajib</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Aktif</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse($fields as $f)
                <tr class="border-t border-rule hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs">{{ $f->sort_order }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $f->field_name }}</td>
                    <td class="px-4 py-3 font-serif text-sm">{{ $f->field_label }}</td>
                    <td class="px-4 py-3"><span class="elite-kicker text-[.55rem]">{{ $f->field_type }}</span></td>
                    <td class="px-4 py-3 text-xs">{{ $f->is_required ? '✓' : '—' }}</td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('admin.ppdb.form-builder.update', $f) }}" class="inline">@csrf @method('PUT')
                        <input type="hidden" name="is_active" value="{{ $f->is_active ? 0 : 1 }}">
                        <button class="text-xs {{ $f->is_active ? 'text-green-700' : 'text-gray-400' }}">{{ $f->is_active ? '● Aktif' : '○ Nonaktif' }}</button>
                        </form>
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <form method="POST" action="{{ route('admin.ppdb.form-builder.destroy', $f) }}" class="inline" onsubmit="return confirm('Hapus field ini?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
                    </td>
                </tr>
                @if($f->options)
                <tr class="border-t border-rule bg-gray-50/50"><td colspan="7" class="px-4 py-2">
                    <div class="text-[.6rem] text-gray-500">Opsi: {{ implode(' · ', $f->options) }}</div>
                </td></tr>
                @endif
                @empty
                <tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada field. Tambahkan di panel sebelah kiri.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Pilih periode PPDB terlebih dahulu untuk mengelola form builder.</div>
@endif
@endsection
