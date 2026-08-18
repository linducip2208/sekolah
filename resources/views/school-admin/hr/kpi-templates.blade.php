@extends('layouts.school-admin')
@section('title', 'Template KPI')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="flex justify-between items-end">
        <div>
            <div class="elite-kicker mb-2">Templata Praestantiae</div>
            <h1 class="elite-h1 text-3xl ink-primary mb-2">Template KPI</h1>
            <div class="elite-rule"></div>
            <p class="font-serif text-sm text-gray-600 mt-3">Template penilaian kinerja dengan kriteria bobot.</p>
        </div>
        <a href="{{ route('admin.hr.kpi.index') }}" class="btn-elite-ghost">Appraisal →</a>
    </div>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <form method="POST" action="{{ route('admin.hr.kpi.templates.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Nama Template</label>
                    <input name="name" required maxlength="200" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="PKG Guru / PKG TU">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Skor Maksimum</label>
                    <input type="number" name="max_score" value="100" min="1" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
                </div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Buat Template</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-4">
        @forelse($templates as $t)
        <div class="bg-white border border-rule overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b border-rule flex justify-between items-center">
                <div>
                    <span class="font-serif font-semibold text-sm">{{ $t->name }}</span>
                    <span class="text-xs text-gray-500 ml-2">Maks: {{ $t->max_score }}</span>
                </div>
                <form method="POST" action="{{ route('admin.hr.kpi.templates.destroy', $t) }}" class="inline" onsubmit="return confirm('Hapus?')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-700 hover:underline">Hapus</button>
                </form>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs">
                    <tr>
                        <th class="text-left px-4 py-2">Kriteria</th>
                        <th class="text-center px-4 py-2">Bobot</th>
                        <th class="text-center px-4 py-2">Max Skor</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($t->criteria as $c)
                    <tr class="border-t border-rule">
                        <td class="px-4 py-2 font-serif text-xs">{{ $c->name }}</td>
                        <td class="px-4 py-2 text-center text-xs">{{ $c->weight }}</td>
                        <td class="px-4 py-2 text-center text-xs">{{ $c->max_score }}</td>
                        <td class="px-4 py-2 text-right">
                            <form method="POST" action="{{ route('admin.hr.kpi.criteria.destroy', $c) }}" class="inline" onsubmit="return confirm('Hapus?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-700 hover:underline">×</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <details class="border-t border-rule"><summary class="px-4 py-2 cursor-pointer text-xs underline ink-secondary">+ Tambah Kriteria</summary>
                <form method="POST" action="{{ route('admin.hr.kpi.criteria.store', $t) }}" class="px-4 pb-3 grid grid-cols-4 gap-2 items-end">
                    @csrf
                    <input name="name" required placeholder="Nama kriteria" class="col-span-2 border border-rule px-2 py-1 text-xs">
                    <input type="number" name="weight" value="1" min="1" placeholder="Bobot" class="border border-rule px-2 py-1 font-mono text-xs">
                    <button class="btn-elite" style="padding:.2rem;font-size:.6rem;">Tambah</button>
                </form>
            </details>
        </div>
        @empty
        <div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada template KPI.</div>
        @endforelse
    </div>
</div>

@endsection
