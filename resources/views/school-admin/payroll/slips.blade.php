@extends('layouts.school-admin')
@section('title', 'Slip Gaji')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="flex justify-between items-end">
        <div>
            <div class="elite-kicker mb-2">Schedulae Salarii</div>
            <h1 class="elite-h1 text-3xl ink-primary mb-2">Slip Gaji Bulanan</h1>
            <div class="elite-rule"></div>
        </div>
        <a href="{{ route('admin.payroll.structures.index') }}" class="btn-elite-ghost">← Komponen Gaji</a>
    </div>
</div>

<form method="GET" class="bg-white border border-rule p-5 mb-5 flex gap-3 items-end">
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Bulan</label>
        <input type="month" name="month" value="{{ $month }}" class="border-2 border-rule px-3 py-2 font-mono text-sm">
    </div>
    <button class="btn-elite" style="padding:.6rem 1rem;font-size:.65rem;">Tampilkan</button>
</form>

<form method="POST" action="{{ route('admin.payroll.slips.generate') }}" class="bg-white border border-rule p-5 mb-6 flex gap-3 items-center">
    @csrf
    <input type="hidden" name="month" value="{{ $month }}">
    <span class="font-serif text-sm text-gray-700 flex-1">Generate slip untuk semua staff bulan <strong>{{ $month }}</strong> menggunakan komponen gaji aktif.</span>
    <button class="btn-elite-gold">Generate Bulk</button>
</form>

<div class="bg-white border border-rule overflow-hidden">
    <div class="bg-[var(--c-primary)] text-white px-4 py-3 elite-kicker text-[.65rem]">{{ $slips->count() }} slip · {{ $month }}</div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Staff</th>
                <th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Gapok</th>
                <th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Tunjangan</th>
                <th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Potongan</th>
                <th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Take-home</th>
                <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
                <th class="px-3 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($slips as $sl)
                <tr class="border-t border-rule hover:bg-gray-50">
                    <td class="px-3 py-3 font-serif">{{ $sl->staff?->user?->name }}</td>
                    <td class="px-3 py-3 text-right font-mono text-xs">{{ number_format($sl->basic_salary/100, 0, ',', '.') }}</td>
                    <td class="px-3 py-3 text-right font-mono text-xs text-green-700">+{{ number_format($sl->total_allowances/100, 0, ',', '.') }}</td>
                    <td class="px-3 py-3 text-right font-mono text-xs text-red-700">-{{ number_format($sl->total_deductions/100, 0, ',', '.') }}</td>
                    <td class="px-3 py-3 text-right font-display ink-primary">Rp {{ number_format($sl->net_salary/100, 0, ',', '.') }}</td>
                    <td class="px-3 py-3">
                        @if($sl->status === 'paid')
                            <span class="text-xs text-green-700">✓ Paid {{ $sl->paid_on?->format('d M') }}</span>
                        @else
                            <span class="text-xs text-yellow-700">Draft</span>
                        @endif
                    </td>
                    <td class="px-3 py-3 text-right whitespace-nowrap">
                        @if($sl->status === 'draft')
                            <form method="POST" action="{{ route('admin.payroll.slips.pay', $sl) }}" class="inline">
                                @csrf
                                <button class="text-xs underline ink-secondary hover:ink-accent">Tandai Paid</button>
                            </form>
                            <form method="POST" action="{{ route('admin.payroll.slips.destroy', $sl) }}" class="inline ml-2" onsubmit="return confirm('Hapus?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-700 hover:underline">Hapus</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada slip untuk bulan {{ $month }}. Klik Generate Bulk.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
