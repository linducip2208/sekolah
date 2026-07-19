@extends('layouts.school-admin')
@section('title', 'Benchmark Yayasan')
@section('sidebar')@include('school-admin.partials.sidebar-extended')@endsection

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="mb-7">
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Benchmark Yayasan</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Perbandingan metrik sekolah Anda dengan rata-rata yayasan.</p>
</div>

{{-- Period selector --}}
<form method="GET" class="bg-white border border-rule p-5 mb-6 flex gap-4 items-end">
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Periode</label>
        <input type="month" name="period" value="{{ $period }}" class="border-2 border-rule px-3 py-2 font-mono text-sm">
    </div>
    <button class="btn-elite" style="padding:.6rem 1rem;font-size:.65rem;">Tampilkan</button>
</form>

{{-- Self vs Average KPI cards --}}
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach($data['metrics'] as $metric)
        @php
            $self = $data['self'][$metric->metric_key] ?? null;
            $avg = $data['average'][$metric->metric_key] ?? null;
            $diff = $self && $avg ? $self['value'] - $avg['avg'] : null;
        @endphp
        <div class="bg-white border border-rule p-5 hover:shadow-md cursor-pointer"
             onclick="showTrendChart('{{ $metric->metric_key }}')"
             title="Klik untuk lihat tren">
            <div class="elite-kicker text-[.55rem] text-gray-500 mb-1">{{ $metric->metric_name }}</div>
            <div class="flex items-end gap-3">
                <div class="font-display text-2xl ink-primary">
                    {{ $self ? number_format($self['value'], 1) : '-' }}
                    <span class="text-xs text-gray-400 font-normal">{{ $metric->unit }}</span>
                </div>
                @if($diff !== null)
                    <div class="text-xs font-mono {{ $diff >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 1) }}
                        <span class="text-[.6rem] text-gray-400">vs avg</span>
                    </div>
                @endif
            </div>
            @if($self && isset($self['rank']))
                <div class="mt-1 text-xs text-gray-500 font-mono">Peringkat #{{ $self['rank'] }} &middot; Percentile {{ $self['percentile'] }}%</div>
            @endif
            <div class="mt-2 text-[.6rem] text-gray-400">Rata-rata yayasan: {{ $avg ? number_format($avg['avg'], 1) : '-' }}</div>
        </div>
    @endforeach
</div>

{{-- Comparison Bar Chart --}}
<div class="bg-white border border-rule p-7 mb-6">
    <h2 class="elite-h3 text-xl ink-primary mb-4">{{ $data['school']->name }} vs Rata-rata Yayasan</h2>
    <canvas id="comparisonChart" height="120"></canvas>
</div>

{{-- Trend Modal --}}
<div x-data="{ open: false, metricKey: '' }" x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center" style="background: rgba(11,29,58,.6);"
     @click.outside="open = false">
    <div class="bg-white w-full max-w-2xl mx-4 p-6 border border-rule shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="elite-h3 text-xl ink-primary">Tren Historis</h3>
            <button @click="open = false" class="text-gray-500 hover:text-red-600 text-xl">&times;</button>
        </div>
        <canvas id="schoolTrendChart" height="180"></canvas>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const labels = [];
    const selfValues = [];
    const avgValues = [];
    @foreach($data['metrics'] as $metric)
        @php $self = $data['self'][$metric->metric_key] ?? null; $avg = $data['average'][$metric->metric_key] ?? null; @endphp
        labels.push('{{ $metric->metric_name }}');
        selfValues.push({{ $self['value'] ?? 'null' }});
        avgValues.push({{ $avg['avg'] ?? 'null' }});
    @endforeach

    const ctx = document.getElementById('comparisonChart');
    if (ctx && labels.length > 0) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '{{ $data["school"]->name }}',
                        data: selfValues,
                        backgroundColor: 'rgba(37,99,235,0.7)',
                        borderColor: 'rgba(37,99,235,1)',
                        borderWidth: 1,
                    },
                    {
                        label: 'Rata-rata Yayasan',
                        data: avgValues,
                        backgroundColor: 'rgba(184,134,11,0.4)',
                        borderColor: 'rgba(184,134,11,1)',
                        borderWidth: 1,
                        borderDash: [4, 4],
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { font: { family: 'Inter' } } } },
                scales: {
                    y: { beginAtZero: true, ticks: { font: { family: 'Inter' } } },
                    x: { ticks: { font: { family: 'Inter', size: 9 } } },
                },
            },
        });
    }
})();

let schoolTrendInstance = null;

async function showTrendChart(metricKey) {
    const modal = document.querySelector('[x-data]')?._x_dataStack?.[0];
    if (!modal) return;
    modal.metricKey = metricKey;
    modal.open = true;

    try {
        const res = await fetch('{{ route("admin.foundation.benchmark.trend") }}?metric_key=' + metricKey + '&months=12');
        const data = await res.json();

        setTimeout(() => {
            const ctx = document.getElementById('schoolTrendChart');
            if (!ctx) return;
            if (schoolTrendInstance) { schoolTrendInstance.destroy(); schoolTrendInstance = null; }
            schoolTrendInstance = new Chart(ctx, {
                type: 'line',
                data: { labels: data.labels, datasets: data.datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { font: { family: 'Inter' } } } },
                    scales: {
                        y: { beginAtZero: false },
                        x: { ticks: { font: { family: 'Inter', size: 10 } } },
                    },
                },
            });
        }, 100);
    } catch (e) { console.error(e); }
}
</script>
@endpush
