@extends('layouts.school-admin')
@section('title', 'Rekonsiliasi Bank')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Reconciliatio</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Rekonsiliasi Bank</h1>
    <div class="elite-rule"></div>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<div class="grid sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white border border-rule p-4"><div class="elite-kicker text-[.55rem] text-gray-500">Belum Dicocokkan</div><div class="font-display text-2xl text-amber-700 mt-1">{{ $summary['unmatched_count'] }} baris</div></div>
    <div class="bg-white border border-rule p-4"><div class="elite-kicker text-[.55rem] text-gray-500">Sudah Dicocokkan</div><div class="font-display text-2xl text-green-700 mt-1">{{ $summary['matched_count'] }} baris</div></div>
    <div class="bg-white border border-rule p-4"><div class="elite-kicker text-[.55rem] text-gray-500">Kredit Belum Dicocokkan</div><div class="font-display text-2xl ink-primary mt-1">Rp {{ number_format($summary['unmatched_credit']/100, 0, ',', '.') }}</div></div>
</div>

<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Import Rekening Koran</summary>
    <form method="POST" action="{{ route('admin.accounting.bank-reconciliation.import') }}" class="px-5 py-5 border-t border-rule">@csrf
        <div class="mb-3">
            <label class="elite-kicker text-[.6rem] block mb-1">Nama Rekening</label>
            <input name="bank_account" required maxlength="100" placeholder="Mis. BCA 1234567890" class="w-full max-w-xs border-2 border-rule px-3 py-2 font-mono text-sm">
        </div>
        <div class="elite-kicker text-[.6rem] mb-2">Baris Transaksi (jumlah positif = kredit/masuk, negatif = debit/keluar)</div>
        <div id="stmt-container" class="space-y-2">
            <div class="grid grid-cols-[auto_1fr_auto_auto] gap-2 stmt-line">
                <input type="date" name="lines[0][transaction_date]" required class="border-2 border-rule px-2 py-1.5 font-mono text-xs">
                <input name="lines[0][description]" placeholder="Deskripsi" class="border-2 border-rule px-2 py-1.5 font-serif text-xs">
                <input name="lines[0][reference_no]" placeholder="Ref" class="w-24 border-2 border-rule px-2 py-1.5 font-mono text-xs">
                <input type="number" name="lines[0][amount]" step="0.01" required placeholder="Jumlah (Rp)" class="w-32 border-2 border-rule px-2 py-1.5 font-mono text-xs">
            </div>
        </div>
        <button type="button" onclick="addStmtLine()" class="text-xs underline ink-secondary mt-2">+ Tambah baris</button>
        <div class="mt-5"><button class="btn-elite">Import</button></div>
    </form>
</details>

<form method="GET" class="bg-white border border-rule p-4 mb-4 flex gap-2 items-center">
    <select name="status" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">— Semua status —</option>
        <option value="unmatched" @selected(request('status') === 'unmatched')>Belum dicocokkan</option>
        <option value="matched" @selected(request('status') === 'matched')>Sudah dicocokkan</option>
    </select>
    <button class="btn-elite" style="padding:.5rem 1rem;font-size:.65rem;">Filter</button>
</form>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white"><tr>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tanggal</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Deskripsi</th>
            <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Jumlah</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Cocok Dengan</th>
            <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Status</th>
            <th class="px-4 py-3"></th>
        </tr></thead>
        <tbody>
            @forelse($statements as $st)
            <tr class="border-t border-rule hover:bg-gray-50">
                <td class="px-4 py-3 font-mono text-xs">{{ $st->transaction_date->format('d M Y') }}</td>
                <td class="px-4 py-3 font-serif text-xs">{{ Str::limit($st->description, 50) }}<div class="text-gray-400 text-[.6rem]">{{ $st->bank_account }}</div></td>
                <td class="px-4 py-3 text-right font-mono text-xs {{ $st->amount >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ $st->amount >= 0 ? '+' : '' }}Rp {{ number_format($st->amount/100, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-xs">{{ $st->feePayment?->invoice?->student?->user?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-center">
                    @if($st->status === 'matched')<span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-800">✓ Cocok</span>
                    @else<span class="text-xs px-2 py-0.5 rounded bg-amber-100 text-amber-800">Belum</span>@endif
                </td>
                <td class="px-4 py-3 text-right">
                    @if($st->status === 'unmatched')
                    <details class="inline-block"><summary class="text-xs underline cursor-pointer ink-secondary">Cocokkan</summary>
                        <form method="POST" action="{{ route('admin.accounting.bank-reconciliation.match', $st) }}" class="mt-2">@csrf
                            <select name="fee_payment_id" required class="border-2 border-rule px-2 py-1 text-xs">
                                <option value="">— pilih pembayaran —</option>
                                @foreach($unmatchedPayments as $p)
                                    @if($p->amount === abs($st->amount))
                                    <option value="{{ $p->id }}">{{ $p->payment_date->format('d M') }} · {{ $p->invoice?->student?->user?->name }} · Rp {{ number_format($p->amount/100,0,',','.') }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <button class="text-xs ink-accent">Simpan</button>
                        </form></details>
                    @else
                    <form method="POST" action="{{ route('admin.accounting.bank-reconciliation.unmatch', $st) }}" class="inline">@csrf<button class="text-xs text-red-700 hover:underline">Batalkan</button></form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada data rekening koran.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $statements->links() }}</div>

<script>
function addStmtLine() {
    const c = document.getElementById('stmt-container');
    const idx = c.querySelectorAll('.stmt-line').length;
    const row = document.createElement('div');
    row.className = 'grid grid-cols-[auto_1fr_auto_auto] gap-2 stmt-line';
    row.innerHTML = `<input type="date" name="lines[${idx}][transaction_date]" required class="border-2 border-rule px-2 py-1.5 font-mono text-xs">
    <input name="lines[${idx}][description]" placeholder="Deskripsi" class="border-2 border-rule px-2 py-1.5 font-serif text-xs">
    <input name="lines[${idx}][reference_no]" placeholder="Ref" class="w-24 border-2 border-rule px-2 py-1.5 font-mono text-xs">
    <input type="number" name="lines[${idx}][amount]" step="0.01" required placeholder="Jumlah (Rp)" class="w-32 border-2 border-rule px-2 py-1.5 font-mono text-xs">`;
    c.appendChild(row);
}
</script>

@endsection
