@extends('layouts.school-admin')
@section('title', 'Fee Structure')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Structurae Pretiorum</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Struktur Biaya / SPP</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Definisikan jenis biaya: SPP bulanan, uang gedung, kegiatan, dll.</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Tambah Struktur</h3>
            @if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('admin.fee.structures.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Nama</label>
                    <input name="name" required maxlength="200" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="SPP Bulanan">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Frekuensi</label>
                    <select name="frequency" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="monthly">Bulanan</option>
                        <option value="semester">Semester</option>
                        <option value="yearly">Tahunan</option>
                        <option value="one_time">Sekali Bayar</option>
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Jumlah (Rp)</label>
                    <input type="number" step="1000" min="0" name="amount_rupiah" required class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" placeholder="250000">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Khusus Kelas</label>
                    <select name="class_room_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— semua kelas —</option>
                        @foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
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
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Frekuensi</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Jumlah</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kelas</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($structures as $s)
                        <tr class="border-t border-rule">
                            <td class="px-4 py-3 font-serif font-semibold ink-primary">{{ $s->name }}</td>
                            <td class="px-4 py-3"><span class="elite-kicker text-[.55rem]">{{ ucfirst($s->frequency) }}</span></td>
                            <td class="px-4 py-3 font-mono">Rp {{ number_format($s->amount/100, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $s->classRoom?->name ?? 'Semua' }}</td>
                            <td class="px-4 py-3">
                                @if($s->is_active)<span class="text-xs text-green-700">● Aktif</span>
                                @else<span class="text-xs text-gray-500">Off</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.fee.structures.destroy', $s) }}" class="inline" onsubmit="return confirm('Hapus?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada struktur biaya.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
