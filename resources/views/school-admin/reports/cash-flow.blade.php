@extends('layouts.school-admin')
@section('title', 'Cash Flow Chart')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

@push('head')<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>@endpush

<div class="mb-7"><h1 class="elite-h1 text-3xl ink-primary mb-2">Cash Flow Bulanan</h1><div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">Pemasukan vs pengeluaran 12 bulan terakhir.</p></div>

<div class="bg-white border border-rule p-7 mb-6">
<canvas id="cashFlowChart" height="100"></canvas>
</div>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Bulan</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Pemasukan</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Pengeluaran</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Net</th>
</tr></thead><tbody>
@foreach($data as $d)<tr class="border-t border-rule">
<td class="px-3 py-3 font-mono">{{ $d['month'] }}</td>
<td class="px-3 py-3 text-right font-mono text-green-700">+{{ number_format($d['income']/100, 0, ',', '.') }}</td>
<td class="px-3 py-3 text-right font-mono text-red-700">-{{ number_format($d['expense']/100, 0, ',', '.') }}</td>
<td class="px-3 py-3 text-right font-mono font-bold {{ $d['net'] >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ number_format($d['net']/100, 0, ',', '.') }}</td>
</tr>@endforeach
</tbody></table></div>

<script>
window.addEventListener('DOMContentLoaded', () => {
const ctx = document.getElementById('cashFlowChart').getContext('2d');
const data = @json($data);
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: data.map(d => d.month),
        datasets: [
            { label: 'Pemasukan', data: data.map(d => d.income/100), backgroundColor: 'rgba(22, 163, 74, 0.7)' },
            { label: 'Pengeluaran', data: data.map(d => d.expense/100), backgroundColor: 'rgba(220, 38, 38, 0.7)' },
            { label: 'Net', type: 'line', data: data.map(d => d.net/100), borderColor: '#b8860b', backgroundColor: 'rgba(184, 134, 11, 0.2)', tension: 0.3 }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp '+(v/1000000).toFixed(1)+'jt' } } }
    }
});
});
</script>
@endsection
