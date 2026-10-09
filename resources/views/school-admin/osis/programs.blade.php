@extends('layouts.school-admin')
@section('title', 'Program Kerja OSIS')
@section('sidebar')
    @include('school-admin.partials.sidebar')
@endsection

@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Kesiswaan</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Program Kerja OSIS</h1>
    <div class="elite-rule"></div>
</div>

{{-- Create form --}}
<div class="bg-white border border-rule p-5 mb-7">
    <div class="elite-kicker text-[.6rem] mb-3">Tambah Program</div>
    <form method="POST" action="{{ route('admin.osis.programs.store') }}" class="grid md:grid-cols-2 gap-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold mb-1">Judul *</label>
            <input type="text" name="title" required maxlength="255" value="{{ old('title') }}" class="w-full border-2 border-rule px-3 py-2 text-sm">
            @error('title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-xs font-semibold mb-1">Pemilihan terkait</label>
            <select name="osis_election_id" class="w-full border-2 border-rule px-3 py-2 text-sm">
                <option value="">— Tanpa pemilihan —</option>
                @foreach($elections as $e)<option value="{{ $e->id }}" @selected(old('osis_election_id') == $e->id)>{{ $e->title }}</option>@endforeach
            </select>
            @error('osis_election_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label class="block text-xs font-semibold mb-1">Deskripsi</label>
            <textarea name="description" rows="2" class="w-full border-2 border-rule px-3 py-2 text-sm">{{ old('description') }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-semibold mb-1">Anggaran (Rp)</label>
            <input type="number" name="budget" min="0" value="{{ old('budget') }}" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold mb-1">Mulai</label>
                <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full border-2 border-rule px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Selesai</label>
                <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full border-2 border-rule px-3 py-2 text-sm">
            </div>
        </div>
        <div class="md:col-span-2">
            <button class="btn-elite text-xs">Simpan Program</button>
        </div>
    </form>
</div>

{{-- List --}}
<div class="bg-white border border-rule overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left border-b-2 border-rule">
                <th class="px-3 py-2">Program</th>
                <th class="px-3 py-2">Pemilihan</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2 text-right">Anggaran</th>
                <th class="px-3 py-2 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($programs as $p)
            <tr class="border-b border-rule">
                <td class="px-3 py-2">
                    <div class="font-semibold">{{ $p->title }}</div>
                    @if($p->description)<div class="text-xs text-gray-500">{{ Str::limit($p->description, 80) }}</div>@endif
                </td>
                <td class="px-3 py-2 text-xs">{{ $p->election?->title ?? '—' }}</td>
                <td class="px-3 py-2 text-xs">{{ ucfirst($p->status) }}</td>
                <td class="px-3 py-2 text-right text-xs">{{ $p->budget ? 'Rp ' . number_format($p->budget, 0, ',', '.') : '—' }}</td>
                <td class="px-3 py-2 text-right whitespace-nowrap">
                    <form method="POST" action="{{ route('admin.osis.programs.delete', $p) }}" class="inline" onsubmit="return confirm('Hapus program ini?')">
                        @csrf @method('DELETE')
                        <button class="text-xs underline text-red-600">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500 italic">Belum ada program kerja. Tambahkan melalui form di atas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
