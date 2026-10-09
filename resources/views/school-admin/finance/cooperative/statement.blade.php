@extends('layouts.school-admin')
@section('title', 'Statement Simpanan Anggota')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@section('content')
<div class="mb-6">
    <div class="elite-kicker mb-2">Koperasi</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Statement Simpanan — {{ $targetMember->member_number }}</h1>
    <div class="elite-rule"></div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="border-2 border-rule p-4">
        <div class="elite-kicker text-[.6rem] mb-1">Pokok</div>
        <div class="text-xl font-bold">Rp {{ number_format($statement['total_pokok'], 0, ',', '.') }}</div>
    </div>
    <div class="border-2 border-rule p-4">
        <div class="elite-kicker text-[.6rem] mb-1">Wajib</div>
        <div class="text-xl font-bold">Rp {{ number_format($statement['total_wajib'], 0, ',', '.') }}</div>
    </div>
    <div class="border-2 border-rule p-4">
        <div class="elite-kicker text-[.6rem] mb-1">Sukarela</div>
        <div class="text-xl font-bold">Rp {{ number_format($statement['total_sukarela'], 0, ',', '.') }}</div>
    </div>
    <div class="border-2 border-rule p-4">
        <div class="elite-kicker text-[.6rem] mb-1">Total</div>
        <div class="text-xl font-bold">Rp {{ number_format($statement['grand_total'], 0, ',', '.') }}</div>
    </div>
</div>

<div class="border-2 border-rule overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left border-b-2 border-rule">
                <th class="px-3 py-2">Tanggal</th>
                <th class="px-3 py-2">Jenis</th>
                <th class="px-3 py-2">Tipe</th>
                <th class="px-3 py-2 text-right">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($statement['savings'] as $s)
            <tr class="border-b border-rule">
                <td class="px-3 py-2">{{ $s->transaction_date }}</td>
                <td class="px-3 py-2">{{ ucfirst($s->savings_type) }}</td>
                <td class="px-3 py-2">{{ $s->transaction_type === 'deposit' ? 'Setor' : 'Tarik' }}</td>
                <td class="px-3 py-2 text-right">{{ number_format($s->amount, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-3 py-6 text-center text-gray-500">Belum ada transaksi simpanan untuk anggota ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    <a href="{{ route('admin.cooperative.members') }}" class="text-sm underline">← Kembali ke Anggota</a>
</div>
@endsection
