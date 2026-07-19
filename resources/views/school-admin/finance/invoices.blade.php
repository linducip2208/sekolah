@extends('layouts.school-admin')
@section('title', 'Invoice SPP')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Tabulae Solutionis</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Invoice / Tagihan SPP</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">{{ $invoices->total() }} invoice tercatat.</p>
</div>

{{-- Generate invoice batch --}}
<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Generate Invoice Batch</summary>
    <form method="POST" action="{{ route('admin.fee.invoices.generate') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-4 gap-3">
        @csrf
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Struktur Biaya</label>
            <select name="fee_structure_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <option value="">— pilih —</option>
                @foreach($structures as $s)
                    <option value="{{ $s->id }}">{{ $s->name }} (Rp {{ number_format($s->amount/100, 0, ',', '.') }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Rombel</label>
            <select name="class_section_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <option value="">— Semua Siswa —</option>
                @foreach($classSections as $cs)
                    <option value="{{ $cs->id }}">{{ $cs->classRoom?->name }} {{ $cs->section?->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Periode</label>
            <input type="text" name="period" required maxlength="20" placeholder="2026-05" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Tanggal Jatuh Tempo</label>
            <input type="date" name="due_date" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        </div>
        <div class="md:col-span-4">
            <button class="btn-elite">Generate Invoice</button>
        </div>
    </form>
</details>

<form method="GET" class="bg-white border border-rule p-5 mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="No invoice / nama siswa" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
    <select name="status" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">— Semua Status —</option>
        @foreach(['unpaid','partial','paid','overdue'] as $st)
            <option value="{{ $st }}" @selected(request('status')===$st)>{{ ucfirst($st) }}</option>
        @endforeach
    </select>
    <button class="btn-elite" style="padding:.6rem 1rem;font-size:.65rem;">Filter</button>
</form>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Invoice</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Siswa</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Item</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Periode</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Jatuh Tempo</th>
                <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Tagihan</th>
                <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Dibayar</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $inv)
                <tr class="border-t border-rule hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs">{{ $inv->invoice_no }}</td>
                    <td class="px-4 py-3 font-serif">{{ $inv->student?->user?->name }}</td>
                    <td class="px-4 py-3 text-xs">{{ $inv->feeStructure?->name }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $inv->period }}</td>
                    <td class="px-4 py-3 text-xs">{{ $inv->due_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 font-mono text-right">{{ number_format($inv->amount/100, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 font-mono text-right text-green-700">{{ number_format($inv->paid_amount/100, 0, ',', '.') }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded
                            {{ $inv->status === 'unpaid' ? 'bg-gray-100 text-gray-700' : '' }}
                            {{ $inv->status === 'partial' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $inv->status === 'paid' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $inv->status === 'overdue' ? 'bg-red-100 text-red-700' : '' }}">
                            {{ $inv->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.fee.invoices.show', $inv) }}" class="elite-kicker text-xs ink-secondary hover:ink-accent">Detail →</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="p-10 text-center text-gray-500 italic font-serif">Belum ada invoice. Generate batch dulu.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-5">{{ $invoices->links() }}</div>

@endsection
