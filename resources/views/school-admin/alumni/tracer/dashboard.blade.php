@extends('layouts.school-admin')
@section('title', 'Dashboard Tracer Study')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Alumni</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Dashboard Tracer Study</h1>
    <div class="elite-rule"></div>
</div>

<div class="grid md:grid-cols-4 gap-4 mb-6">
    <div class="elite-card p-5">
        <div class="elite-kicker text-[.55rem] mb-1">Total Alumni</div>
        <div class="font-display text-3xl font-bold ink-primary">{{ $totalAlumni }}</div>
    </div>
    <div class="elite-card p-5">
        <div class="elite-kicker text-[.55rem] mb-1">Sudah Mengisi</div>
        <div class="font-display text-3xl font-bold ink-accent">{{ $totalResponses }}</div>
    </div>
    <div class="elite-card p-5">
        <div class="elite-kicker text-[.55rem] mb-1">Response Rate</div>
        <div class="font-display text-3xl font-bold" style="color:var(--c-primary)">{{ $responseRate }}%</div>
    </div>
    <div class="elite-card p-5">
        <div class="elite-kicker text-[.55rem] mb-1">Relevan Bidang</div>
        <div class="font-display text-3xl font-bold" style="color:#16A34A">{{ $relevant }}/{{ $totalResponses }}</div>
    </div>
</div>

<div class="grid md:grid-cols-2 gap-6 mb-6">
    <div class="elite-card p-5">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Distribusi Status Alumni</h3>
        <canvas id="statusChart"></canvas>
    </div>
    <div class="elite-card p-5">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Relevansi Bidang Kerja</h3>
        <canvas id="relevanceChart"></canvas>
    </div>
</div>

<div class="grid md:grid-cols-2 gap-6 mb-6">
    <div class="elite-card p-5">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Rentang Gaji</h3>
        @if($salaryData->isNotEmpty())
        <canvas id="salaryChart"></canvas>
        @else
        <p class="text-gray-500 font-serif italic">Belum ada data gaji.</p>
        @endif
    </div>
    <div class="elite-card p-5">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Response per Tahun Lulus</h3>
        @if($byYear->isNotEmpty())
        <canvas id="yearChart"></canvas>
        @else
        <p class="text-gray-500 font-serif italic">Belum ada data per tahun.</p>
        @endif
    </div>
</div>

<div class="elite-card p-5 mb-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">10 Respons Terbaru</h3>
    <div class="table-scroll"><table class="w-full text-sm table-elite">
    <thead><tr>
    <th>Nama</th><th>Thn Lulus</th><th>Status</th><th>Perusahaan</th><th>Posisi</th><th>Gaji</th><th>Relevan</th><th>Tanggal</th>
    </tr></thead><tbody>
    @forelse($recentResponses as $r)<tr>
    <td class="font-serif font-semibold">{{ $r->alumniProfile?->user?->name }}</td>
    <td class="font-mono text-xs">{{ $r->graduation_year }}</td>
    <td class="text-xs">{{ $r->status }}</td>
    <td class="text-xs">{{ $r->company_name ?? '—' }}</td>
    <td class="text-xs">{{ $r->position ?? '—' }}</td>
    <td class="text-xs">{{ $r->salary_range ?? '—' }}</td>
    <td class="text-xs text-center">{{ $r->is_relevant === true ? 'Ya' : ($r->is_relevant === false ? 'Tidak' : '—') }}</td>
    <td class="text-xs">{{ $r->submitted_at?->format('d M Y') }}</td>
    </tr>@empty<tr><td colspan="8" class="p-4 text-center text-gray-500 italic font-serif">Belum ada respons.</td></tr>@endforelse
    </tbody></table></div>
</div>

<div class="flex gap-3">
    <a href="{{ route('admin.tracer.responses') }}" class="btn-elite-ghost text-xs">Lihat Semua Respons</a>
    <a href="{{ route('admin.tracer.questions') }}" class="btn-elite-ghost text-xs">Kelola Pertanyaan</a>
    <a href="{{ route('admin.tracer.export-csv') }}" class="btn-elite-ghost text-xs">↓ Ekspor CSV</a>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Distribution Pie Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'pie',
        data: {
            labels: @json($statusCounts->keys()),
            datasets: [{data: @json($statusCounts->values()), backgroundColor: ['#2563EB','#16A34A','#EAB308','#DC2626','#64748B']}]
        },
        options: {
            plugins: {legend: {position: 'bottom', labels: {font: {family: 'Inter', size: 11}}}}
        }
    });

    // Relevance Bar Chart
    new Chart(document.getElementById('relevanceChart'), {
        type: 'bar',
        data: {
            labels: ['Relevan', 'Tidak Relevan', 'Tidak Diketahui'],
            datasets: [{data: [{{ $relevant }}, {{ $notRelevant }}, {{ $unknown }}], backgroundColor: ['#16A34A','#DC2626','#64748B']}]
        },
        options: {
            plugins: {legend: {display: false}},
            scales: {y: {beginAtZero: true, ticks: {stepSize: 1}}}
        }
    });

    @if($salaryData->isNotEmpty())
    new Chart(document.getElementById('salaryChart'), {
        type: 'bar',
        data: {
            labels: @json($salaryData->keys()),
            datasets: [{data: @json($salaryData->values()), backgroundColor: '#2563EB'}]
        },
        options: {
            plugins: {legend: {display: false}},
            scales: {y: {beginAtZero: true, ticks: {stepSize: 1}}}
        }
    });
    @endif

    @if($byYear->isNotEmpty())
    new Chart(document.getElementById('yearChart'), {
        type: 'line',
        data: {
            labels: @json($byYear->keys()),
            datasets: [{data: @json($byYear->values()), borderColor: '#b8860b', backgroundColor: 'rgba(184,134,11,0.1)', fill: true, tension: 0.3}]
        },
        options: {
            plugins: {legend: {display: false}},
            scales: {y: {beginAtZero: true, ticks: {stepSize: 1}}}
        }
    });
    @endif
});
</script>
@endpush
