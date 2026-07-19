@extends('super-admin.layout')

@section('title', 'Langganan')

@section('content')
<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-900">Transaksi Langganan</h2>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Sekolah</th>
                    <th class="px-6 py-3">Plan</th>
                    <th class="px-6 py-3">Jumlah</th>
                    <th class="px-6 py-3">Periode</th>
                    <th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($subscriptions as $tx)
                <tr>
                    <td class="px-6 py-3 text-gray-400">#{{ $tx->id }}</td>
                    <td class="px-6 py-3 font-medium">{{ $tx->school?->name ?? '-' }}</td>
                    <td class="px-6 py-3">{{ $tx->plan?->name ?? '-' }}</td>
                    <td class="px-6 py-3">Rp {{ number_format($tx->amount/100, 0, ',', '.') }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ $tx->period_from }} s/d {{ $tx->period_to }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            {{ $tx->status === 'paid' ? 'bg-green-100 text-green-700' : ($tx->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ ucfirst($tx->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada transaksi.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">{{ $subscriptions->links() }}</div>
    </div>
</div>
@endsection
