@extends('layouts.school-admin')
@section('title', 'Dashboard RKAS')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Contra Rationes Pecuniae</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Dashboard Anggaran (RKAS)</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Ringkasan Rencana Kegiatan dan Anggaran Sekolah.</p>
</div>

<div class="mb-4 flex flex-wrap gap-2 items-center">
    <form method="GET" class="flex gap-2 items-center">
        <select name="academic_year_id" class="border-2 border-rule px-3 py-2 font-serif text-sm" onchange="this.form.submit()">
            <option value="">— Semua Tahun Ajaran —</option>
            @foreach($academicYears as $ay)
                <option value="{{ $ay->id }}" {{ $academicYearId == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="elite-card p-5 text-center">
        <div class="elite-kicker text-[.55rem] mb-1">Total Rencana</div>
        <div class="font-display text-2xl ink-primary">Rp {{ number_format($totalPlanned / 100, 0, ',', '.') }}</div>
    </div>
    <div class="elite-card p-5 text-center">
        <div class="elite-kicker text-[.55rem] mb-1">Total Realisasi</div>
        <div class="font-display text-2xl ink-primary">Rp {{ number_format($totalActual / 100, 0, ',', '.') }}</div>
    </div>
    <div class="elite-card p-5 text-center">
        <div class="elite-kicker text-[.55rem] mb-1">Pendapatan</div>
        <div class="font-display text-2xl text-green-700">Rp {{ number_format($byType['income']['actual'] / 100, 0, ',', '.') }}</div>
        <div class="text-xs text-gray-500 mt-1">Rencana: Rp {{ number_format($byType['income']['planned'] / 100, 0, ',', '.') }}</div>
    </div>
    <div class="elite-card p-5 text-center">
        <div class="elite-kicker text-[.55rem] mb-1">Belanja</div>
        <div class="font-display text-2xl text-red-700">Rp {{ number_format($byType['expense']['actual'] / 100, 0, ',', '.') }}</div>
        <div class="text-xs text-gray-500 mt-1">Rencana: Rp {{ number_format($byType['expense']['planned'] / 100, 0, ',', '.') }}</div>
    </div>
</div>

@if($totalPlanned > 0)
<div class="grid lg:grid-cols-2 gap-6 mb-6">
    <div class="elite-card p-5">
        <h3 class="elite-h3 text-base ink-primary mb-4">Anggaran per Kategori (Rencana vs Realisasi)</h3>
        <canvas id="barChart" height="260"></canvas>
    </div>
    <div class="elite-card p-5">
        <h3 class="elite-h3 text-base ink-primary mb-4">Komposisi Realisasi per Kategori</h3>
        <canvas id="doughnutChart" height="260"></canvas>
    </div>
</div>
@endif

<div class="elite-card overflow-hidden">
    <div class="px-5 py-4 border-b border-rule flex justify-between items-center">
        <h3 class="elite-h3 text-base ink-primary">Progress Item Anggaran</h3>
    </div>
    <div class="table-scroll">
        <table class="w-full text-sm">
            <thead class="bg-[var(--c-primary)] text-white">
                <tr>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Item</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kategori</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Rencana</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Realisasi</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Progress</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr class="border-t border-rule">
                        <td class="px-4 py-3 font-serif font-semibold ink-primary">{{ $item->name }}</td>
                        <td class="px-4 py-3 text-xs">{{ $item->category?->name ?? '-' }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $item->planned_amount_rupiah }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $item->actual_amount_rupiah }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 h-2">
                                    <div class="h-2 {{ $item->progress_percent >= 100 ? 'bg-green-600' : 'bg-[var(--c-accent)]' }}" style="width:{{ $item->progress_percent }}%"></div>
                                </div>
                                <span class="font-mono text-xs font-semibold">{{ $item->progress_percent }}%</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada item anggaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
(function() {
    var ctxBar = document.getElementById('barChart');
    if (ctxBar) {
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: {!! json_encode($categoryChart['labels']) !!},
                datasets: [{
                    label: 'Rencana (Rp)',
                    data: {!! json_encode($categoryChart['planned']) !!},
                    backgroundColor: 'rgba(11,29,58,.7)',
                    borderColor: '#0b1d3a',
                    borderWidth: 1
                }, {
                    label: 'Realisasi (Rp)',
                    data: {!! json_encode($categoryChart['actual']) !!},
                    backgroundColor: 'rgba(184,134,11,.7)',
                    borderColor: '#b8860b',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { labels: { font: { family: 'Inter' } } } },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: function(v) { return 'Rp ' + v.toLocaleString('id-ID'); } } }
                }
            }
        });
    }

    var ctxDough = document.getElementById('doughnutChart');
    if (ctxDough) {
        new Chart(ctxDough, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($doughnutLabels) !!},
                datasets: [{
                    data: {!! json_encode($doughnutData) !!},
                    backgroundColor: ['#0b1d3a','#b8860b','#7a1e2b','#2563eb','#16a34a','#eab308','#8b5cf6','#ec4899','#f97316','#14b8a6'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'right', labels: { font: { family: 'Inter', size: 11 }, padding: 12 } } }
            }
        });
    }
})();
</script>
@endpush

@endsection
