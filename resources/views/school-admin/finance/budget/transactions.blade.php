@extends('layouts.school-admin')
@section('title', 'Transaksi Anggaran')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Acta Rationis</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Transaksi Anggaran</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Pencatatan realisasi dana: pemasukan & pengeluaran.</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Catat Transaksi</h3>
            @if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('admin.budget.transactions.store') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Item Anggaran</label>
                    <select name="budget_item_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">Pilih Item...</option>
                        @foreach($items as $it)
                            <option value="{{ $it->id }}" {{ $itemId == $it->id ? 'selected' : '' }}>{{ $it->name }} ({{ $it->category?->name ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tanggal</label>
                    <input type="date" name="transaction_date" required value="{{ date('Y-m-d') }}" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Jumlah (Rp)</label>
                    <input type="number" step="1000" min="0" name="amount_rp" required class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" placeholder="1000000">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Pembayaran gaji Januari"></textarea>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">No. Referensi</label>
                    <input name="reference_no" maxlength="100" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" placeholder="INV/2024/001">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Bukti (JPG/PDF, max 5MB)</label>
                    <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf" class="w-full border-2 border-rule px-3 py-2 text-sm">
                </div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Catat Transaksi</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white border border-rule overflow-hidden">
            <div class="px-5 py-3 border-b border-rule flex flex-wrap gap-3 items-center justify-between">
                <div class="flex flex-wrap gap-2 items-center">
                    <form method="GET" class="flex flex-wrap gap-2 items-center">
                        <select name="budget_item_id" class="border-2 border-rule px-2 py-1.5 text-xs font-serif" onchange="this.form.submit()">
                            <option value="">— Semua Item —</option>
                            @foreach($items as $it)
                                <option value="{{ $it->id }}" {{ $itemId == $it->id ? 'selected' : '' }}>{{ $it->name }}</option>
                            @endforeach
                        </select>
                        <input type="date" name="from" value="{{ request()->from }}" class="border-2 border-rule px-2 py-1.5 text-xs font-serif" placeholder="Dari">
                        <input type="date" name="to" value="{{ request()->to }}" class="border-2 border-rule px-2 py-1.5 text-xs font-serif" placeholder="Sampai">
                        <input type="text" name="search" value="{{ request()->search }}" class="border-2 border-rule px-2 py-1.5 text-xs font-serif" placeholder="Cari...">
                        <button class="btn-elite" style="padding:.35rem .8rem;font-size:.6rem;letter-spacing:.1em;">Filter</button>
                    </form>
                </div>
                <a href="{{ route('admin.budget.export', ['type' => 'transactions'] + request()->all()) }}" class="text-xs text-[var(--c-accent)] hover:underline">Export CSV</a>
            </div>
            <div class="table-scroll">
                <table class="w-full text-sm">
                    <thead class="bg-[var(--c-primary)] text-white">
                        <tr>
                            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tanggal</th>
                            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Item</th>
                            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kategori</th>
                            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Jumlah</th>
                            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Ref</th>
                            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Deskripsi</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr class="border-t border-rule">
                                <td class="px-4 py-3 font-mono text-xs">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 font-serif text-sm font-semibold ink-primary">{{ $tx->budgetItem?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-xs">{{ $tx->budgetItem?->category?->name ?? '-' }}</td>
                                <td class="px-4 py-3 font-mono text-xs {{ $tx->amount > 0 ? 'text-green-700' : '' }}">{{ $tx->amount_rupiah }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $tx->reference_no ?? '-' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-600">{{ Str::limit($tx->description, 40) }}
                                    @if($tx->receipt_path)
                                        <a href="{{ Storage::disk('public')->url($tx->receipt_path) }}" target="_blank" class="text-[var(--c-accent)] hover:underline ml-1">📎</a>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.budget.transactions.destroy', $tx) }}" class="inline" onsubmit="return confirm('Hapus transaksi?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-rule">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>

@endsection
