@extends('layouts.school-admin')
@section('title', 'Komponen Gaji')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="flex justify-between items-end">
        <div>
            <div class="elite-kicker mb-2">Componenta Salarii</div>
            <h1 class="elite-h1 text-3xl ink-primary mb-2">Komponen Gaji</h1>
            <div class="elite-rule"></div>
            <p class="font-serif text-sm text-gray-600 mt-3">Tunjangan dan potongan tetap untuk perhitungan slip gaji.</p>
        </div>
        <a href="{{ route('admin.payroll.slips.index') }}" class="btn-elite-ghost">Slip Gaji →</a>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <form method="POST" action="{{ route('admin.payroll.structures.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Nama</label>
                    <input name="name" required maxlength="200" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Tunjangan Transportasi / BPJS">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tipe</label>
                    <select name="type" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="allowance">Tunjangan (+)</option>
                        <option value="deduction">Potongan (-)</option>
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Cara Hitung</label>
                    <select name="calculation" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="fixed">Nominal Tetap (Rp)</option>
                        <option value="percentage">Persentase dari Gapok (%)</option>
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Nilai</label>
                    <input type="number" step="any" min="0" name="value" required class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" placeholder="500000 atau 5">
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
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tipe</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Cara</th>
                        <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Nilai</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($structures as $s)
                        <tr class="border-t border-rule">
                            <td class="px-4 py-3 font-serif font-semibold">{{ $s->name }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-0.5 rounded {{ $s->type === 'allowance' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $s->type === 'allowance' ? 'Tunjangan' : 'Potongan' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs">{{ $s->calculation === 'fixed' ? 'Tetap' : 'Persen' }}</td>
                            <td class="px-4 py-3 text-right font-mono">
                                @if($s->calculation === 'fixed')
                                    Rp {{ number_format($s->value/100, 0, ',', '.') }}
                                @else
                                    {{ $s->value }}%
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.payroll.structures.destroy', $s) }}" class="inline" onsubmit="return confirm('Hapus?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada komponen.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
