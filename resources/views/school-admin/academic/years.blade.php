@extends('layouts.school-admin')
@section('title', 'Tahun Ajaran')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Anni Academici</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Tahun Ajaran</h1>
    <div class="elite-rule"></div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6" x-data="{ editing: null }">
            <h3 class="elite-h3 text-base ink-primary mb-3">Tambah Tahun Ajaran</h3>
            @if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('admin.academic.years.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Nama (e.g. 2024/2025)</label>
                    <input name="name" required maxlength="50" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="2025/2026">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tanggal Selesai</label>
                    <input type="date" name="end_date" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white border border-rule overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white">
                    <tr>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Periode</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($years as $y)
                        <tr class="border-t border-rule {{ $y->is_active ? 'bg-[rgba(184,134,11,.06)]' : '' }}">
                            <td class="px-4 py-3 font-serif font-semibold ink-primary">{{ $y->name }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600">{{ $y->start_date->format('d M Y') }} → {{ $y->end_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                @if($y->is_active)
                                    <span class="text-xs font-semibold text-green-700">● Aktif</span>
                                @else
                                    <span class="text-xs text-gray-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if(!$y->is_active)
                                    <form method="POST" action="{{ route('admin.academic.years.activate', $y) }}" class="inline">
                                        @csrf
                                        <button class="text-xs underline ink-secondary hover:ink-accent">Aktifkan</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.academic.years.destroy', $y) }}" class="inline ml-2"
                                          onsubmit="return confirm('Hapus tahun ajaran ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada tahun ajaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
