@extends('layouts.school-admin')

@section('title', 'AI Usage & Cost')

@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">AI Assistant</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Penggunaan & Biaya AI</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Lacak konsumsi token & estimasi biaya AI di sekolah Anda.</p>
</div>

<form method="GET" class="bg-white border border-rule p-4 mb-4 flex gap-3 items-end text-sm">
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Dari</label>
        <input type="date" name="date_from" value="{{ $from }}" class="border-2 border-rule px-2 py-1.5">
    </div>
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Sampai</label>
        <input type="date" name="date_to" value="{{ $to }}" class="border-2 border-rule px-2 py-1.5">
    </div>
    <button class="btn-elite text-sm">Filter</button>
</form>

<div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-6">
    <div class="bg-white border border-rule p-3"><div class="elite-kicker text-[.55rem]">Total Calls</div><div class="text-2xl font-display ink-primary">{{ number_format($kpis->calls ?? 0) }}</div></div>
    <div class="bg-white border border-rule p-3"><div class="elite-kicker text-[.55rem]">Token Input</div><div class="text-2xl font-display ink-primary">{{ number_format($kpis->input_tokens ?? 0) }}</div></div>
    <div class="bg-white border border-rule p-3"><div class="elite-kicker text-[.55rem]">Token Output</div><div class="text-2xl font-display ink-primary">{{ number_format($kpis->output_tokens ?? 0) }}</div></div>
    <div class="bg-white border border-rule p-3"><div class="elite-kicker text-[.55rem]">Estimasi Biaya</div><div class="text-2xl font-display ink-primary">{{ money(($kpis->total_cost ?? 0) * 100) }}</div><div class="text-[.65rem] text-gray-500">USD raw: {{ number_format($kpis->total_cost ?? 0, 4) }}</div></div>
    <div class="bg-white border border-rule p-3"><div class="elite-kicker text-[.55rem]">Avg Latency</div><div class="text-2xl font-display ink-primary">{{ number_format($kpis->avg_latency ?? 0, 0) }}ms</div></div>
    <div class="bg-white border border-rule p-3"><div class="elite-kicker text-[.55rem]">Error</div><div class="text-2xl font-display text-red-700">{{ number_format($kpis->errors ?? 0) }}</div></div>
</div>

<div class="grid lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white border border-rule p-4">
        <h3 class="elite-h3 text-lg mb-3">Per Fitur</h3>
        <table class="w-full text-xs">
            <thead class="border-b border-rule">
                <tr>
                    <th class="text-left py-1 elite-kicker text-[.55rem]">Fitur</th>
                    <th class="text-right py-1 elite-kicker text-[.55rem]">Calls</th>
                    <th class="text-right py-1 elite-kicker text-[.55rem]">Token In</th>
                    <th class="text-right py-1 elite-kicker text-[.55rem]">Token Out</th>
                    <th class="text-right py-1 elite-kicker text-[.55rem]">Cost</th>
                </tr>
            </thead>
            <tbody>
                @forelse($byFeature as $row)
                    <tr class="border-b border-rule">
                        <td class="py-1.5 font-mono">{{ $row->feature_key }}</td>
                        <td class="py-1.5 text-right">{{ number_format($row->calls) }}</td>
                        <td class="py-1.5 text-right">{{ number_format($row->input_tokens) }}</td>
                        <td class="py-1.5 text-right">{{ number_format($row->output_tokens) }}</td>
                        <td class="py-1.5 text-right font-semibold">{{ number_format($row->total_cost, 4) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-3 text-center text-gray-500">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white border border-rule p-4">
        <h3 class="elite-h3 text-lg mb-3">Per Model</h3>
        <table class="w-full text-xs">
            <thead class="border-b border-rule">
                <tr>
                    <th class="text-left py-1 elite-kicker text-[.55rem]">Model</th>
                    <th class="text-right py-1 elite-kicker text-[.55rem]">Calls</th>
                    <th class="text-right py-1 elite-kicker text-[.55rem]">Cost</th>
                </tr>
            </thead>
            <tbody>
                @forelse($byModel as $row)
                    <tr class="border-b border-rule">
                        <td class="py-1.5 font-mono">{{ $row->aiModel?->display_name ?? $row->aiModel?->model_name ?? '#'.$row->ai_model_id }}</td>
                        <td class="py-1.5 text-right">{{ number_format($row->calls) }}</td>
                        <td class="py-1.5 text-right font-semibold">{{ number_format($row->total_cost, 4) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-3 text-center text-gray-500">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white border border-rule p-4 mb-6">
    <h3 class="elite-h3 text-lg mb-3">Tren Harian</h3>
    <canvas id="aiDaily" height="80"></canvas>
</div>

@if($recentErrors->count())
<div class="bg-white border border-rule p-4">
    <h3 class="elite-h3 text-lg mb-3 text-red-700">Error Terakhir</h3>
    <ul class="text-xs font-mono space-y-1">
        @foreach($recentErrors as $e)
            <li class="border-b border-rule py-1">
                <span class="text-gray-500">{{ $e->created_at?->format('d/m H:i:s') }}</span>
                <span class="text-amber-700">[{{ $e->feature_key }}]</span>
                {{ \Illuminate\Support\Str::limit($e->error, 200) }}
            </li>
        @endforeach
    </ul>
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('aiDaily');
if (ctx) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($dailySeries->pluck('day')),
            datasets: [
                { label: 'Calls', data: @json($dailySeries->pluck('calls')), borderColor: '#2563EB', tension: 0.3 },
                { label: 'Cost (USD)', data: @json($dailySeries->pluck('total_cost')), borderColor: '#DC2626', tension: 0.3, yAxisID: 'y1' },
            ]
        },
        options: {
            scales: {
                y:  { type: 'linear', position: 'left' },
                y1: { type: 'linear', position: 'right', grid: { drawOnChartArea: false } },
            }
        }
    });
}
</script>
@endsection
