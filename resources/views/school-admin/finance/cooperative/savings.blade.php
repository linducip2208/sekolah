@extends('layouts.school-admin')
@section('title', 'Simpanan Koperasi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@section('content')
<div class="mb-6">
    <div class="elite-kicker mb-2">Koperasi</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Simpanan Anggota</h1>
    <div class="elite-rule"></div>
</div>

<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="elite-card p-4">
        <div class="elite-kicker text-[.6rem]">Total Setoran</div>
        <div class="font-display text-2xl ink-primary">Rp {{ number_format($depositTotal / 100, 0, ',', '.') }}</div>
    </div>
    <div class="elite-card p-4">
        <div class="elite-kicker text-[.6rem]">Total Penarikan</div>
        <div class="font-display text-2xl ink-primary">Rp {{ number_format($withdrawalTotal / 100, 0, ',', '.') }}</div>
    </div>
</div>

<button onclick="document.getElementById('addSavingForm').classList.toggle('hidden')" class="btn-elite-gold mb-4">+ Catat Simpanan</button>

<div id="addSavingForm" class="hidden elite-card p-6 mb-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Catat Simpanan</h3>
    <form method="POST" action="{{ route('admin.cooperative.savings.store') }}" class="grid md:grid-cols-2 gap-4">
        @csrf
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Anggota *</label>
            <select name="cooperative_member_id" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                <option value="">— Pilih —</option>
                @foreach($members as $m)<option value="{{ $m->id }}">{{ $m->member_number }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Tgl Transaksi *</label>
            <input type="date" name="transaction_date" required value="{{ date('Y-m-d') }}" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Jumlah (Rp) *</label>
            <input type="number" name="amount" required min="1" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Jenis *</label>
            <select name="savings_type" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                <option value="pokok">Simpanan Pokok</option>
                <option value="wajib">Simpanan Wajib</option>
                <option value="sukarela">Simpanan Sukarela</option>
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Tipe Transaksi *</label>
            <select name="transaction_type" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                <option value="deposit">Setor</option>
                <option value="withdrawal">Tarik</option>
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Catatan</label>
            <input type="text" name="notes" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="btn-elite">Simpan</button>
        </div>
    </form>
</div>

<form method="GET" class="flex gap-3 mb-4 bg-white border border-rule p-4">
    <select name="type" class="border-2 border-rule px-3 py-2 text-sm">
        <option value="">— Jenis —</option>
        <option value="pokok" {{ request('type') === 'pokok' ? 'selected' : '' }}>Pokok</option>
        <option value="wajib" {{ request('type') === 'wajib' ? 'selected' : '' }}>Wajib</option>
        <option value="sukarela" {{ request('type') === 'sukarela' ? 'selected' : '' }}>Sukarela</option>
    </select>
    <select name="member" class="border-2 border-rule px-3 py-2 text-sm">
        <option value="">— Anggota —</option>
        @foreach($members as $m)<option value="{{ $m->id }}" {{ request('member') == $m->id ? 'selected' : '' }}>{{ $m->member_number }}</option>@endforeach
    </select>
    <button type="submit" class="btn-elite-ghost text-xs">Filter</button>
</form>

<div class="elite-card overflow-hidden">
    <div class="table-scroll">
    <table class="table-elite w-full text-sm">
        <thead>
            <tr>
                <th>Anggota</th>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Tipe</th>
                <th>Jumlah</th>
                <th>Ref</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($savings as $saving)
            <tr>
                <td class="font-mono text-[.65rem]">{{ $saving->member?->member_number }}</td>
                <td class="text-xs">{{ $saving->transaction_date->format('d/m/Y') }}</td>
                <td class="text-[.6rem] uppercase">{{ $saving->savings_type }}</td>
                <td><span class="text-[.6rem] px-2 py-0.5 rounded {{ $saving->transaction_type === 'deposit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $saving->transaction_type === 'deposit' ? 'Setor' : 'Tarik' }}</span></td>
                <td class="font-mono text-xs">Rp {{ number_format($saving->amount / 100, 0, ',', '.') }}</td>
                <td class="text-xs font-mono">{{ $saving->reference_no ?? '—' }}</td>
                <td class="text-right">
                    <form method="POST" action="{{ route('admin.cooperative.savings.delete', $saving) }}" class="inline" onsubmit="return confirm('Hapus transaksi?')">
                        @csrf @method('DELETE')
                        <button class="text-xs underline text-red-600">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada transaksi simpanan.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-4">{{ $savings->links() }}</div>
@endsection
