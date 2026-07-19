@extends('layouts.parent')
@section('title', 'Raport Interaktif — ' . $student->user?->name)
@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
@section('content')

<div class="max-w-6xl mx-auto px-4 py-8">
    <a href="{{ route('portal.dashboard') }}" class="text-xs ink-secondary hover:ink-accent">← Kembali ke Portal</a>
    <div class="mt-3 mb-7">
        <div class="elite-kicker mb-2">Tabula Progressus</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Raport: {{ $student->user?->name }}</h1>
        <p class="text-sm text-gray-600 font-serif">No. Induk: {{ $student->admission_no }} · Kelas: {{ $student->classSection?->classRoom?->name }} {{ $student->classSection?->section?->name }}</p>
        <div class="elite-rule"></div>
    </div>

    @if($reportCard)
        <div class="elite-card p-6 mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="elite-kicker text-[.55rem] mb-1">Nilai Rata-Rata</div>
                    <div class="font-display text-3xl font-bold ink-accent">{{ number_format($reportCard->gpa ?? 0, 2) }}</div>
                </div>
                <div class="text-center">
                    <div class="elite-kicker text-[.55rem] mb-1">Peringkat</div>
                    <div class="font-display text-3xl font-bold ink-primary">{{ $reportCard->rank ?? '-' }}</div>
                </div>
                <div class="text-center">
                    <div class="elite-kicker text-[.55rem] mb-1">Nilai Akhir</div>
                    <div class="font-display text-3xl font-bold {{ ($reportCard->overall_grade ?? '') === 'A' ? 'text-green-700' : 'ink-primary' }}">
                        {{ $reportCard->overall_grade ?? '-' }}
                    </div>
                </div>
                <div class="text-center">
                    <div class="elite-kicker text-[.55rem] mb-1">Semester</div>
                    <div class="font-serif text-lg">{{ $selectedSemester?->name }}</div>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6 mb-6">
            @if($chartData)
            <div class="elite-card p-6">
                <h3 class="elite-h3 text-base ink-primary mb-4">Perbandingan Nilai</h3>
                <canvas id="subjectChart" height="200"></canvas>
            </div>
            @endif

            @if($attendanceData)
            <div class="elite-card p-6">
                <h3 class="elite-h3 text-base ink-primary mb-4">Kehadiran</h3>
                <div class="flex items-center gap-6">
                    <canvas id="attendanceChart" height="180" class="max-w-[180px]"></canvas>
                    <div class="space-y-2 text-sm">
                        <div><span class="w-3 h-3 bg-green-500 inline-block rounded-full mr-2"></span>Hadir: {{ $attendanceData['hadir'] }}</div>
                        <div><span class="w-3 h-3 bg-blue-500 inline-block rounded-full mr-2"></span>Izin: {{ $attendanceData['izin'] }}</div>
                        <div><span class="w-3 h-3 bg-yellow-500 inline-block rounded-full mr-2"></span>Sakit: {{ $attendanceData['sakit'] }}</div>
                        <div><span class="w-3 h-3 bg-red-500 inline-block rounded-full mr-2"></span>Alpa: {{ $attendanceData['alpa'] }}</div>
                        <div><span class="w-3 h-3 bg-orange-500 inline-block rounded-full mr-2"></span>Terlambat: {{ $attendanceData['terlambat'] }}</div>
                    </div>
                </div>
            </div>
            @endif

            @if($progressData && count($progressData['labels']) > 1)
            <div class="elite-card p-6">
                <h3 class="elite-h3 text-base ink-primary mb-4">Progress GPA</h3>
                <canvas id="progressChart" height="200"></canvas>
            </div>
            @endif
        </div>

        @if($reportCard->teacher_notes)
        <div class="elite-card p-6 mb-6">
            <h3 class="elite-h3 text-base ink-primary mb-2">Catatan Wali Kelas</h3>
            <p class="text-sm font-serif text-gray-700 leading-relaxed">{{ $reportCard->teacher_notes }}</p>
        </div>
        @endif
    @else
        <div class="elite-card p-10 text-center">
            <p class="text-gray-500 font-serif text-lg italic">Belum ada data raport untuk semester ini.</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if(isset($chartData) && count($chartData['labels']) > 0)
    new Chart(document.getElementById('subjectChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartData['labels']) !!},
            datasets: [
                { label: 'Nilai', data: {!! json_encode($chartData['obtained']) !!}, backgroundColor: 'rgba(184,134,11,0.7)', borderColor: '#b8860b', borderWidth: 1, borderRadius: 4 },
                { label: 'Total', data: {!! json_encode($chartData['total']) !!}, backgroundColor: 'rgba(11,29,58,0.12)', borderColor: 'rgba(11,29,58,0.4)', borderWidth: 1, borderRadius: 4 }
            ]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } }
        }
    });
    @endif

    @if(isset($attendanceData))
    new Chart(document.getElementById('attendanceChart'), {
        type: 'doughnut',
        data: {
            labels: ['Hadir','Izin','Sakit','Alpa','Terlambat'],
            datasets: [{ data: [{{ $attendanceData['hadir'] }},{{ $attendanceData['izin'] }},{{ $attendanceData['sakit'] }},{{ $attendanceData['alpa'] }},{{ $attendanceData['terlambat'] }}], backgroundColor: ['rgba(34,197,94,0.8)','rgba(59,130,246,0.8)','rgba(234,179,8,0.8)','rgba(239,68,68,0.8)','rgba(249,115,22,0.8)'], borderWidth: 1 }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
    @endif

    @if(isset($progressData) && count($progressData['labels']) > 1)
    new Chart(document.getElementById('progressChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($progressData['labels']) !!},
            datasets: [{ label: 'GPA', data: {!! json_encode($progressData['gpa']) !!}, borderColor: '#0b1d3a', backgroundColor: 'rgba(11,29,58,0.1)', borderWidth: 2.5, pointRadius: 5, pointBackgroundColor: '#b8860b', tension: 0.3, fill: true }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, max: 4.0, ticks: { stepSize: 0.5 } } },
            plugins: { legend: { display: false } }
        }
    });
    @endif
});
</script>
@endpush

@stop
