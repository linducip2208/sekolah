@extends('layouts.parent')
@section('title', 'Tagihan Saya')
@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-4 py-3 border-b">
        <h2 class="font-bold">Tagihan Belum Lunas</h2>
        <p class="text-sm text-gray-600">Klik "Bayar" untuk membayar online via VA, QRIS, e-wallet, atau transfer manual.</p>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-600">
            <tr>
                <th class="px-4 py-2">No. Invoice</th>
                <th class="px-4 py-2">Periode</th>
                <th class="px-4 py-2">Jatuh Tempo</th>
                <th class="px-4 py-2">Jumlah</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($invoices as $inv)
                <tr>
                    <td class="px-4 py-3 font-mono text-xs">{{ $inv->invoice_no }}</td>
                    <td class="px-4 py-3">{{ $inv->period }}</td>
                    <td class="px-4 py-3">{{ optional($inv->due_date)->format('d M Y') }}</td>
                    <td class="px-4 py-3 font-medium">Rp {{ number_format(($inv->amount - $inv->discount - $inv->paid_amount) / 100, 0, ',', '.') }}</td>
                    <td class="px-4 py-3">
                        @if($inv->status === 'partial')
                            <span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded text-xs">Sebagian</span>
                        @elseif($inv->status === 'overdue')
                            <span class="px-2 py-0.5 bg-red-100 text-red-800 rounded text-xs">Terlambat</span>
                        @else
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-800 rounded text-xs">Belum bayar</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('portal.invoices.pay', $inv->id) }}" class="btn-brand text-xs px-3 py-1">Bayar</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-gray-500">Tidak ada tagihan tertunda. 🎉</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
