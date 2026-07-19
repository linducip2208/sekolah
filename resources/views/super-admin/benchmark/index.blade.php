@extends('super-admin.layout')
@section('title', 'Benchmark Antar Sekolah')
@section('content')

<div class="mb-7">
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Benchmark Antar Sekolah</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Perbandingan kinerja antar sekolah dalam satu yayasan.</p>
</div>

{{-- Foundation + Period Selector --}}
<form method="GET" class="bg-white border border-rule p-5 mb-6 flex flex-wrap gap-4 items-end">
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Yayasan</label>
        <select name="foundation_id" class="border-2 border-rule px-3 py-2 font-serif text-sm min-w-[240px]">
            <option value="">-- Pilih Yayasan --</option>
            @foreach($foundations as $f)
                <option value="{{ $f->id }}" {{ $foundationId == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Periode</label>
        <input type="month" name="period" value="{{ $period }}" class="border-2 border-rule px-3 py-2 font-mono text-sm">
    </div>
    <button class="btn-elite" style="padding:.6rem 1rem;font-size:.65rem;">Tampilkan</button>
</form>

@if($data)
    {{-- Radar Chart: Multi-school comparison --}}
    <div class="bg-white border border-rule p-7 mb-6">
        <h2 class="elite-h3 text-xl ink-primary mb-4">Perbandingan Multi-Sekolah &mdash; {{ $data['foundation']->name }}</h2>
        <canvas id="radarChart" height="300"></canvas>
    </div>

    {{-- Ranking Table --}}
    <div class="bg-white border border-rule overflow-hidden mb-6">
        <table class="w-full text-sm">
            <thead class="bg-[var(--c-primary)] text-white">
                <tr>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Sekolah</th>
                    <th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Rata-rata Peringkat</th>
                    @foreach($data['metrics'] as $m)
                        <th class="text-center px-3 py-3 elite-kicker text-[.55rem]">{{ $m['metric_name'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data['rankings'] as $rank)
                    <tr class="border-t border-rule hover:bg-gray-50">
                        <td class="px-4 py-3 font-serif font-semibold">{{ $rank['school_name'] }}</td>
                        <td class="text-center px-3 py-3 font-display text-lg ink-primary">
                            {{ $rank['avg_rank'] ? '#' . $rank['avg_rank'] : '-' }}
                        </td>
                        @foreach($data['metrics'] as $m)
                            @php
                                $val = $data['schools'][$rank['school_id']]['metrics'][$m['metric_key']] ?? null;
                            @endphp
                            <td class="text-center px-3 py-3 font-mono text-xs cursor-pointer hover:text-[var(--c-accent)]"
                                onclick="showTrend({{ $rank['school_id'] }}, '{{ $m['metric_key'] }}')"
                                title="Klik untuk lihat tren">
                                {{ $val['value'] !== null ? number_format($val['value'], 1) : '-' }}
                                @if($val['rank'])
                                    <span class="text-[.6rem] text-gray-400">#{{ $val['rank'] }}</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Drill-down: Historical Trend Modal --}}
    <div x-data="trendModal()" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background: rgba(11,29,58,.6);">
        <div @click.outside="open = false" class="bg-white w-full max-w-2xl mx-4 p-6 border border-rule shadow-xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="elite-h3 text-xl ink-primary">Tren Historis &mdash; <span x-text="metricLabel"></span></h3>
                <button @click="open = false" class="text-gray-500 hover:text-red-600 text-xl">&times;</button>
            </div>
            <canvas id="trendChart" height="180"></canvas>
        </div>
    </div>
@else
    <div class="bg-white border border-rule p-10 text-center">
        <p class="font-serif text-lg text-gray-500 italic">Pilih yayasan dan periode untuk melihat benchmark.</p>
    </div>
@endif

@endsection

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@push('scripts')
<script>
@if($data)
(function() {
    const radarData = @json($data['radar_data']);
    const ctx = document.getElementById('radarChart');
    if (ctx && radarData.labels.length > 0) {
        new Chart(ctx, {
            type: 'radar',
            data: radarData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        ticks: { font: { family: 'Inter', size: 10 } },
                        pointLabels: { font: { family: 'Inter', size: 11 } },
                    },
                },
                plugins: {
                    legend: { position: 'bottom', labels: { font: { family: 'Inter' }, padding: 16 } },
                },
            },
        });
    }
})();
@endif

function trendModal() {
    return {
        open: false,
        metricLabel: '',
        trendChartInstance: null,
    };
}

async function showTrend(schoolId, metricKey) {
    const modal = document.querySelector('[x-data="trendModal()"]')?._x_dataStack?.[0];
    if (!modal) return;

    try {
        const res = await fetch(`{{ route("super.benchmark.drilldown") }}?school_id=${schoolId}&metric_key=${metricKey}&months=12`);
        const data = await res.json();

        if (modal.trendChartInstance) { modal.trendChartInstance.destroy(); modal.trendChartInstance = null; }

        modal.metricLabel = data.metric?.metric_name || metricKey;
        modal.open = true;

        setTimeout(() => {
            const ctx = document.getElementById('trendChart');
            if (!ctx) return;
            modal.trendChartInstance = new Chart(ctx, {
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
