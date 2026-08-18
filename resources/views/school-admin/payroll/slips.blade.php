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
        <div class="flex gap-2">
            <a href="{{ route('admin.payroll.bpjs.index') }}" class="btn-elite-ghost" style="font-size:.65rem;">BPJS Config</a>
            <a href="{{ route('admin.payroll.pph21.index') }}" class="btn-elite-ghost" style="font-size:.65rem;">PPh21</a>
            <a href="{{ route('admin.payroll.tax-profiles.index') }}" class="btn-elite-ghost" style="font-size:.65rem;">Profil Pajak</a>
            <a href="{{ route('admin.payroll.structures.index') }}" class="btn-elite-ghost">← Komponen Gaji</a>
        </div>
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
    <span class="font-serif text-sm text-gray-700 flex-1">Generate slip untuk semua staff bulan <strong>{{ $month }}</strong> — otomatis hitung BPJS & PPh21.</span>
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
                <th class="text-right px-3 py-3 elite-kicker text-[.6rem]">BPJS (员工)</th>
                <th class="text-right px-3 py-3 elite-kicker text-[.6rem]">PPh21</th>
                <th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Potongan Lain</th>
                <th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Take-home</th>
                <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
                <th class="px-3 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($slips as $sl)
                @php
                    $bpjsTotal = 0; $pph21Amt = 0; $otherDeductions = 0;
                    foreach(($sl->deductions_detail ?? []) as $d) {
                        if(in_array($d['name'], ['BPJS Kesehatan (员工)', 'JHT (员工)', 'JP (员工)', 'BPJS Kes', 'JHT', 'JP'])) $bpjsTotal += $d['amount'];
                        elseif($d['name'] === 'PPh21') $pph21Amt = $d['amount'];
                        else $otherDeductions += $d['amount'];
                    }
                @endphp
                <tr class="border-t border-rule hover:bg-gray-50">
                    <td class="px-3 py-3 font-serif">{{ $sl->staff?->user?->name }}</td>
                    <td class="px-3 py-3 text-right font-mono text-xs">{{ number_format($sl->basic_salary/100, 0, ',', '.') }}</td>
                    <td class="px-3 py-3 text-right font-mono text-xs text-green-700">+{{ number_format($sl->total_allowances/100, 0, ',', '.') }}</td>
                    <td class="px-3 py-3 text-right font-mono text-xs text-orange-700">-{{ number_format($bpjsTotal/100, 0, ',', '.') }}</td>
                    <td class="px-3 py-3 text-right font-mono text-xs text-red-700">-{{ number_format($pph21Amt/100, 0, ',', '.') }}</td>
                    <td class="px-3 py-3 text-right font-mono text-xs text-red-700">-{{ number_format($otherDeductions/100, 0, ',', '.') }}</td>
                    <td class="px-3 py-3 text-right font-display ink-primary">Rp {{ number_format($sl->net_salary/100, 0, ',', '.') }}</td>
                    <td class="px-3 py-3">
                        @if($sl->status === 'paid')
                            <span class="text-xs text-green-700">Paid {{ $sl->paid_on?->format('d M') }}</span>
                        @else
                            <span class="text-xs text-yellow-700">Draft</span>
                        @endif
                    </td>
                    <td class="px-3 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('admin.print.salary-slip', $sl) }}" target="_blank" class="text-xs underline ink-secondary hover:ink-accent">PDF</a>
                        @if($sl->status === 'draft')
                            <form method="POST" action="{{ route('admin.payroll.slips.pay', $sl) }}" class="inline ml-2">@csrf
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
                <tr><td colspan="9" class="p-10 text-center text-gray-500 italic font-serif">Belum ada slip untuk bulan {{ $month }}. Klik Generate Bulk.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
