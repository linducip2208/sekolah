@extends('layouts.school-admin')
@section('title', 'Master Data Yayasan')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7"><div class="elite-kicker mb-2">Yayasan</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Master Data Pusat</h1>
<div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">Kelola data master yayasan dan sync ke sekolah cabang.</p></div>

<form method="GET" class="bg-white border border-rule p-4 mb-6 flex gap-3 items-end flex-wrap">
<div>
    <label class="elite-kicker text-[.6rem] block mb-1">Tipe Data</label>
    <select name="data_type" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        @foreach(['subject'=>'Mata Pelajaran','class_template'=>'Template Kelas','fee_template'=>'Template SPP','grading_scale'=>'Grading Scale'] as $val => $label)
        <option value="{{ $val }}" @selected($dataType === $val)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<button class="btn-elite" style="padding:.6rem 1rem;font-size:.65rem;">Tampilkan</button>
</form>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Tambah Data</h3>
            <form method="POST" action="{{ route('admin.foundation.master-data.store') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="data_type" value="{{ $dataType }}">
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Data (JSON)</label>
                    <textarea name="data_json" required rows="6" class="w-full border-2 border-rule px-3 py-2 font-mono text-xs" placeholder='{"name":"Matematika","code":"MTK"}'></textarea>
                </div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white border border-rule overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white"><tr>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Data</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Sync</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tanggal</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse($items as $item)
                <tr class="border-t border-rule hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs max-w-xs truncate">{{ json_encode($item->data_json) }}</td>
                    <td class="px-4 py-3">
                        @if($item->is_synced)
                            <span class="text-xs text-green-700">✓ Synced</span>
                        @else
                            <form method="POST" action="{{ route('admin.foundation.master-data.sync', $item) }}" class="inline">@csrf
                                <button class="text-xs underline ink-secondary hover:ink-accent">Tandai Sync</button>
                            </form>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $item->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('admin.foundation.master-data.destroy', $item) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada data master.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
