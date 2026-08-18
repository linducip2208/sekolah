@extends('layouts.school-admin')
@section('title', 'Bracket PPh21')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="flex justify-between items-end">
        <div>
            <div class="elite-kicker mb-2">Progressivum Tributum</div>
            <h1 class="elite-h1 text-3xl ink-primary mb-2">Bracket PPh21</h1>
            <div class="elite-rule"></div>
            <p class="font-serif text-sm text-gray-600 mt-3">Atur tarif pajak penghasilan pasal 21 progresif.</p>
        </div>
        <a href="{{ route('admin.payroll.slips.index') }}" class="btn-elite-ghost">← Slip Gaji</a>
    </div>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <form method="POST" action="{{ route('admin.payroll.pph21.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Gaji Minimum (Rp/tahun)</label>
                    <input type="number" name="min_annual_rupiah" required class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" placeholder="0">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Gaji Maksimum (Rp/tahun, kosong = tanpa batas)</label>
                    <input type="number" name="max_annual_rupiah" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" placeholder="Tak terbatas">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tarif (%)</label>
                    <input type="number" step="0.01" name="rate_pct" min="0" max="100" required class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" placeholder="5">
                </div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Tambah Bracket</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white border border-rule overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white">
                    <tr>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Minimum</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Maksimum</th>
                        <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Tarif</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brackets as $b)
                        <tr class="border-t border-rule">
                            <td class="px-4 py-3 font-mono text-xs">Rp {{ number_format($b->min_annual/100, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $b->max_annual ? 'Rp ' . number_format($b->max_annual/100, 0, ',', '.') : 'Tak terbatas' }}</td>
                            <td class="px-4 py-3 text-right font-mono font-semibold">{{ number_format($b->rate_pct/100, 2, '.', '') }}%</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.payroll.pph21.destroy', $b) }}" class="inline" onsubmit="return confirm('Hapus?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada bracket. Sistem menggunakan default PPh21 2024.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 bg-white border border-rule p-5">
            <div class="elite-kicker text-[.7rem] mb-3">Default PPh21 2024 (jika bracket kosong)</div>
            <table class="w-full text-xs">
                <thead><tr class="border-b border-rule">
                    <th class="text-left py-2">Penghasilan Kena Pajak</th>
                    <th class="text-right py-2">Tarif</th>
                </tr></thead>
                <tbody class="font-mono">
                    <tr><td class="py-1">Rp 0 – Rp 60.000.000</td><td class="text-right">5%</td></tr>
                    <tr><td class="py-1">Rp 60.000.000 – Rp 250.000.000</td><td class="text-right">15%</td></tr>
                    <tr><td class="py-1">Rp 250.000.000 – Rp 500.000.000</td><td class="text-right">25%</td></tr>
                    <tr><td class="py-1">Rp 500.000.000+</td><td class="text-right">30%</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
