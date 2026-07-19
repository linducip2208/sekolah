@extends('super-admin.layout')
@section('title', 'Analytics Platform')
@section('content')

<div class="mb-7"><div class="elite-kicker mb-2">Analyses</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Analytics Platform</h1><div class="elite-rule"></div></div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
<div class="bg-white border-l-4 border-purple-600 p-5">
<div class="elite-kicker text-[.6rem]">Total Revenue</div>
<div class="font-display text-2xl ink-primary mt-2">Rp {{ number_format($totalRevenue/100, 0, ',', '.') }}</div>
</div>
<div class="bg-white border-l-4 border-blue-600 p-5">
<div class="elite-kicker text-[.6rem]">Total Sekolah</div>
<div class="font-display text-2xl ink-primary mt-2">{{ number_format($totalSchools) }}</div>
</div>
<div class="bg-white border-l-4 border-green-600 p-5">
<div class="elite-kicker text-[.6rem]">Total Siswa</div>
<div class="font-display text-2xl ink-primary mt-2">{{ number_format($totalStudents) }}</div>
</div>
<div class="bg-white border-l-4 border-yellow-600 p-5">
<div class="elite-kicker text-[.6rem]">Total Guru</div>
<div class="font-display text-2xl ink-primary mt-2">{{ number_format($totalTeachers) }}</div>
</div>
</div>

<div class="grid lg:grid-cols-2 gap-6 mb-6">
<div class="bg-white border border-rule p-7">
<h3 class="elite-h3 text-lg ink-primary mb-4">Revenue 12 Bulan Terakhir</h3>
<canvas id="revenueChart" height="160"></canvas>
</div>

<div class="bg-white border border-rule p-7">
<h3 class="elite-h3 text-lg ink-primary mb-4">Pertumbuhan Sekolah & Siswa</h3>
<canvas id="growthChart" height="160"></canvas>
</div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="bg-white border border-rule p-7">
<h3 class="elite-h3 text-lg ink-primary mb-4">Distribusi Plan</h3>
<canvas id="planChart" height="200"></canvas>
</div>

<div class="lg:col-span-2 bg-white border border-rule p-7">
<h3 class="elite-h3 text-lg ink-primary mb-4">Tabel Detail Revenue per Bulan</h3>
<table class="w-full text-sm"><thead><tr class="border-b border-rule">
<th class="text-left py-2 elite-kicker text-[.6rem]">Bulan</th>
<th class="text-right py-2 elite-kicker text-[.6rem]">Total Revenue</th>
</tr></thead><tbody>
@forelse($revenue as $r)<tr class="border-b border-rule last:border-0">
<td class="py-2 font-mono">{{ $r->month }}</td>
<td class="py-2 text-right font-mono ink-primary">Rp {{ number_format($r->total/100, 0, ',', '.') }}</td>
</tr>@empty<tr><td colspan="2" class="py-10 text-center text-gray-500 italic">Belum ada transaksi.</td></tr>@endforelse
</tbody></table>
</div>
</div>

<script>
window.addEventListener('DOMContentLoaded', () => {
    const revData = @json($revenue);
    const growthData = @json($growth);
    const planData = @json($planDist);

    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: revData.map(d => d.month),
            datasets: [{
                label: 'Revenue (Rp)',
                data: revData.map(d => d.total / 100),
                backgroundColor: 'rgba(184, 134, 11, 0.7)',
                borderColor: '#b8860b',
                borderWidth: 1
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } },
            scales: { y: { ticks: { callback: v => 'Rp '+(v/1000000).toFixed(1)+'jt' } } } }
    });

    new Chart(document.getElementById('growthChart'), {
        type: 'line',
        data: {
            labels: growthData.map(d => d.month),
            datasets: [
                { label: 'Sekolah Baru', data: growthData.map(d => d.new_schools), borderColor: '#0b1d3a', backgroundColor: 'rgba(11,29,58,.1)', tension: 0.3, fill: true },
                { label: 'Siswa Baru', data: growthData.map(d => d.new_students), borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,.1)', tension: 0.3, fill: true }
            ]
        },
        options: { responsive: true }
    });

    new Chart(document.getElementById('planChart'), {
        type: 'doughnut',
        data: {
            labels: planData.map(d => d.plan),
            datasets: [{
                data: planData.map(d => d.count),
                backgroundColor: ['#16a34a', '#3b82f6', '#b8860b', '#dc2626', '#7c3aed']
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
});
</script>
@endsection
