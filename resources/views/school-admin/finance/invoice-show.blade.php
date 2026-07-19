@extends('layouts.school-admin')
@section('title', 'Detail Invoice')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.fee.invoices.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kembali</a>

<div class="flex justify-between items-start mb-7">
    <div>
        <div class="elite-kicker mb-2">{{ $invoice->invoice_no }}</div>
        <h1 class="elite-h1 text-2xl ink-primary mb-2">Invoice — {{ $invoice->student?->user?->name }}</h1>
        <div class="elite-rule"></div>
    </div>
    <span class="text-sm px-3 py-2 rounded font-semibold uppercase
        {{ $invoice->status === 'unpaid' ? 'bg-gray-100 text-gray-700' : '' }}
        {{ $invoice->status === 'partial' ? 'bg-yellow-100 text-yellow-700' : '' }}
        {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : '' }}
        {{ $invoice->status === 'overdue' ? 'bg-red-100 text-red-700' : '' }}">{{ $invoice->status }}</span>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border border-rule p-7">
            <h3 class="elite-h3 text-lg ink-primary mb-4">Detail Tagihan</h3>
            <table class="w-full text-sm">
                <tr class="border-b border-rule"><td class="py-2 elite-kicker text-[.6rem] w-40">Item</td><td class="py-2 font-serif">{{ $invoice->feeStructure?->name }}</td></tr>
                <tr class="border-b border-rule"><td class="py-2 elite-kicker text-[.6rem]">Periode</td><td class="py-2 font-mono">{{ $invoice->period }}</td></tr>
                <tr class="border-b border-rule"><td class="py-2 elite-kicker text-[.6rem]">Jatuh Tempo</td><td class="py-2">{{ $invoice->due_date->translatedFormat('d F Y') }}</td></tr>
                <tr class="border-b border-rule"><td class="py-2 elite-kicker text-[.6rem]">Total Tagihan</td><td class="py-2 font-display text-xl ink-primary">Rp {{ number_format($invoice->amount/100, 0, ',', '.') }}</td></tr>
                <tr class="border-b border-rule"><td class="py-2 elite-kicker text-[.6rem]">Sudah Dibayar</td><td class="py-2 font-display text-xl text-green-700">Rp {{ number_format($invoice->paid_amount/100, 0, ',', '.') }}</td></tr>
                <tr><td class="py-2 elite-kicker text-[.6rem]">Sisa</td><td class="py-2 font-display text-xl text-red-700">Rp {{ number_format(($invoice->amount - $invoice->paid_amount)/100, 0, ',', '.') }}</td></tr>
            </table>
        </div>

        <div class="bg-white border border-rule p-7">
            <h3 class="elite-h3 text-lg ink-primary mb-4">Riwayat Pembayaran</h3>
            @if($invoice->payments->isEmpty())
                <p class="font-serif text-sm text-gray-500 italic">Belum ada pembayaran.</p>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Tanggal</th>
                            <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Metode</th>
                            <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Ref</th>
                            <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Petugas</th>
                            <th class="text-right px-3 py-2 elite-kicker text-[.6rem]">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->payments as $p)
                            <tr class="border-t border-rule">
                                <td class="px-3 py-2 text-xs">{{ $p->payment_date->format('d M Y') }}</td>
                                <td class="px-3 py-2 text-xs">{{ $p->payment_method }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $p->reference ?? '—' }}</td>
                                <td class="px-3 py-2 text-xs">{{ $p->collector?->name ?? '—' }}</td>
                                <td class="px-3 py-2 text-right font-mono">Rp {{ number_format($p->amount/100, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="space-y-4">
        @if($invoice->status !== 'paid')
            <div class="bg-white border border-rule p-6 deco-frame">
                <div class="elite-kicker mb-2" style="color: var(--c-accent);">Catat Pembayaran</div>
                <h4 class="elite-h3 text-base ink-primary mb-3">Tambah Pembayaran</h4>

                @if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

                <form method="POST" action="{{ route('admin.fee.invoices.pay', $invoice) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="elite-kicker text-[.6rem] block mb-1">Jumlah (Rp)</label>
                        <input type="number" step="1000" min="0" name="amount_rupiah" required
                               value="{{ ($invoice->amount - $invoice->paid_amount) / 100 }}"
                               class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
                    </div>
                    <div>
                        <label class="elite-kicker text-[.6rem] block mb-1">Metode</label>
                        <select name="payment_method" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                            <option value="cash">Tunai</option>
                            <option value="bank_transfer">Transfer Bank</option>
                            <option value="qris">QRIS</option>
                            <option value="va">Virtual Account</option>
                            <option value="ewallet">E-Wallet</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="elite-kicker text-[.6rem] block mb-1">Tanggal</label>
                        <input type="date" name="payment_date" required value="{{ now()->toDateString() }}" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    </div>
                    <div>
                        <label class="elite-kicker text-[.6rem] block mb-1">Referensi</label>
                        <input name="reference" maxlength="200" class="w-full border-2 border-rule px-3 py-2 font-mono text-xs" placeholder="No. transaksi">
                    </div>
                    <div>
                        <label class="elite-kicker text-[.6rem] block mb-1">Catatan</label>
                        <textarea name="note" rows="2" maxlength="500" class="w-full border-2 border-rule px-3 py-2 font-serif text-xs"></textarea>
                    </div>
                    <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan Pembayaran</button>
                </form>
            </div>
        @else
            <div class="bg-green-50 border-l-4 border-green-700 p-5">
                <p class="font-serif text-sm text-green-900">✓ Invoice sudah lunas.</p>
            </div>
        @endif

        @if($invoice->paid_amount === 0)
            <form method="POST" action="{{ route('admin.fee.invoices.destroy', $invoice) }}" onsubmit="return confirm('Hapus invoice ini?')">
                @csrf @method('DELETE')
                <button class="text-xs underline text-red-700">Hapus Invoice</button>
            </form>
        @endif
    </div>
</div>

@endsection
