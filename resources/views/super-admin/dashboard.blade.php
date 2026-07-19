@extends('super-admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-900">Dashboard Platform</h2>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-indigo-500">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total Sekolah</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['overview']['total_schools']) }}</p>
            <p class="text-xs text-green-600 mt-1">{{ $stats['overview']['active_schools'] }} aktif</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total Siswa</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['overview']['total_students']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-500">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total Guru</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['overview']['total_teachers']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-purple-500">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Revenue Bulan Ini</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($stats['overview']['total_revenue_this_month_cents'] / 100, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $stats['new_schools_this_month'] }} sekolah baru</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Revenue Chart --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Revenue 12 Bulan Terakhir</h3>
            <canvas id="revenueChart" height="120"></canvas>
        </div>

        {{-- Plan Distribution --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Distribusi Plan</h3>
            <canvas id="planChart" height="160"></canvas>
            <div class="mt-4 space-y-2">
                @foreach((array) ($stats['plan_distribution'] ?? []) as $dist)
                    @php $dist = is_array($dist) ? $dist : (array) $dist; @endphp
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">{{ $dist['plan'] ?? '-' }}</span>
                    <span class="font-semibold">{{ $dist['count'] ?? 0 }} ({{ $dist['percentage'] ?? 0 }}%)</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Expiring Subscriptions --}}
    @if(!empty($stats['subscriptions_expiring_soon']))
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">⚠️ Langganan Akan Berakhir (30 hari)</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-400 border-b">
                        <th class="pb-2">Sekolah</th>
                        <th class="pb-2">Plan</th>
                        <th class="pb-2">Berakhir</th>
                        <th class="pb-2">Sisa Hari</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach((array) ($stats['subscriptions_expiring_soon'] ?? []) as $item)
                        @php $item = is_array($item) ? $item : (array) $item; @endphp
                    <tr class="border-b last:border-0">
                        <td class="py-2 font-medium">{{ $item['school'] ?? '-' }}</td>
                        <td class="py-2 text-gray-500">{{ $item['plan'] ?? '-' }}</td>
                        <td class="py-2 text-red-500">{{ $item['expires_at'] ?? '-' }}</td>
                        <td class="py-2">
                            @php
                                $days = now()->diffInDays(\Carbon\Carbon::parse($item['expires_at']), false);
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $days <= 3 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ $days }} hari
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
const revenueData = @json($stats['monthly_revenue']);
new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: revenueData.map(d => d.month),
        datasets: [{
            label: 'Revenue (Rp)',
            data: revenueData.map(d => d.amount_cents / 100),
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99,102,241,0.1)',
            tension: 0.4,
            fill: true,
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});

const planData = @json($stats['plan_distribution']);
new Chart(document.getElementById('planChart'), {
    type: 'doughnut',
    data: {
        labels: planData.map(d => d.plan),
        datasets: [{ data: planData.map(d => d.count), backgroundColor: ['#6366f1','#10b981','#f59e0b','#ef4444'] }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>
@endpush

@endsection
