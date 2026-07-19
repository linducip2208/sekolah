@extends('layouts.school-admin')
@section('title', 'Raport Interaktif')
@section('sidebar')@include('school-admin.partials.sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Tabula Progressus</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Raport Digital Interaktif</h1>
    <div class="elite-rule"></div>
</div>

{{-- Selector --}}
<form method="GET" action="{{ route('admin.raport-interaktif.index') }}" class="elite-card p-4 mb-6 flex flex-wrap items-end gap-4">
    <div class="flex-1 min-w-[200px]">
        <label class="elite-kicker text-[.6rem] block mb-1">Siswa</label>
        <select name="student_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" required>
            <option value="">-- Pilih Siswa --</option>
            @foreach($students as $s)
                <option value="{{ $s->id }}" {{ $selectedStudent && $selectedStudent->id == $s->id ? 'selected' : '' }}>
                    {{ $s->admission_no }} — {{ $s->user?->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-[200px]">
        <label class="elite-kicker text-[.6rem] block mb-1">Semester</label>
        <select name="semester_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" required>
            <option value="">-- Pilih Semester --</option>
            @foreach($semesters as $sem)
                <option value="{{ $sem->id }}" {{ $selectedSemester && $selectedSemester->id == $sem->id ? 'selected' : '' }}>
                    {{ $sem->name }} ({{ $sem->academicYear?->name }})
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <button type="submit" class="btn-elite">Tampilkan</button>
    </div>
    <div>
        <button type="button" onclick="window.print()" class="btn-elite-ghost">Cetak PDF</button>
    </div>
</form>

@if($selectedStudent && $selectedSemester && $reportCard)
    {{-- Student Info Header --}}
    <div class="elite-card p-6 mb-6 no-print">
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <div class="elite-kicker text-[.55rem] mb-1">Nama Siswa</div>
                <div class="font-display text-xl ink-primary">{{ $selectedStudent->user?->name }}</div>
            </div>
            <div>
                <div class="elite-kicker text-[.55rem] mb-1">No. Induk</div>
                <div class="font-serif text-lg">{{ $selectedStudent->admission_no }}</div>
            </div>
            <div>
                <div class="elite-kicker text-[.55rem] mb-1">Semester</div>
                <div class="font-serif text-lg">{{ $selectedSemester->name }} ({{ $selectedSemester->academicYear?->name }})</div>
            </div>
            <div>
                <div class="elite-kicker text-[.55rem] mb-1">Nilai Rata-Rata</div>
                <div class="font-display text-2xl font-bold ink-accent">{{ number_format($reportCard->gpa ?? 0, 2) }}</div>
            </div>
            <div>
                <div class="elite-kicker text-[.55rem] mb-1">Peringkat</div>
                <div class="font-display text-2xl font-bold ink-primary">{{ $reportCard->rank ?? '-' }}</div>
            </div>
            <div>
                <div class="elite-kicker text-[.55rem] mb-1">Nilai Akhir</div>
                <div class="font-display text-2xl font-bold {{ ($reportCard->overall_grade ?? '') === 'A' ? 'text-green-700' : 'ink-primary' }}">
                    {{ $reportCard->overall_grade ?? '-' }}
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        {{-- Subject Comparison Bar Chart --}}
        @if($chartData)
        <div class="elite-card p-6">
            <h3 class="elite-h3 text-base ink-primary mb-4">Perbandingan Nilai per Mata Pelajaran</h3>
            <canvas id="subjectChart" height="200"></canvas>
        </div>
        @endif

        {{-- Attendance Pie Chart --}}
        @if($attendanceData)
        <div class="elite-card p-6">
            <h3 class="elite-h3 text-base ink-primary mb-4">Ringkasan Kehadiran</h3>
            <div class="flex items-center gap-6">
                <canvas id="attendanceChart" height="180" class="max-w-[180px]"></canvas>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span> Hadir: {{ $attendanceData['hadir'] }}</div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> Izin: {{ $attendanceData['izin'] }}</div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-yellow-500 inline-block"></span> Sakit: {{ $attendanceData['sakit'] }}</div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span> Alpa: {{ $attendanceData['alpa'] }}</div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-orange-500 inline-block"></span> Terlambat: {{ $attendanceData['terlambat'] }}</div>
                </div>
            </div>
        </div>
        @endif

        {{-- Competency Radar Chart --}}
        @if($reportCard->competency_scores)
        <div class="elite-card p-6">
            <h3 class="elite-h3 text-base ink-primary mb-4">Profil Kompetensi</h3>
            <canvas id="competencyChart" height="200"></canvas>
        </div>
        @endif

        {{-- Semester Progress Line Chart --}}
        @if($progressData && count($progressData['labels']) > 1)
        <div class="elite-card p-6">
            <h3 class="elite-h3 text-base ink-primary mb-4">Progress GPA per Semester</h3>
            <canvas id="progressChart" height="200"></canvas>
        </div>
        @endif
    </div>

    {{-- Marks Table --}}
    @if($chartData && count($chartData['labels']) > 0)
    <div class="elite-card overflow-hidden mb-6">
        <div class="px-4 py-3 bg-gray-50 border-b border-rule">
            <h3 class="elite-h3 text-base ink-primary">Detail Nilai</h3>
        </div>
        <div class="table-scroll">
            <table class="w-full text-sm table-elite">
                <thead>
                    <tr>
                        <th class="text-left px-4 py-3">Mata Pelajaran</th>
                        <th class="text-center px-4 py-3">Nilai</th>
                        <th class="text-center px-4 py-3">Total</th>
                        <th class="text-center px-4 py-3">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chartData['labels'] as $i => $label)
                        <tr class="border-t border-rule">
                            <td class="px-4 py-3 font-serif font-semibold ink-primary">{{ $label }}</td>
                            <td class="px-4 py-3 text-center font-semibold">{{ $chartData['obtained'][$i] ?? 0 }}</td>
                            <td class="px-4 py-3 text-center">{{ $chartData['total'][$i] ?? 0 }}</td>
                            <td class="px-4 py-3 text-center">
                                @php $pct = ($chartData['total'][$i] ?? 0) > 0 ? round(($chartData['obtained'][$i] ?? 0) / $chartData['total'][$i] * 100) : 0; @endphp
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $pct >= 75 ? 'bg-green-500' : ($pct >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold">{{ $pct }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Teacher Notes & Extracurricular --}}
    @if($reportCard->teacher_notes || $reportCard->extracurricular_notes)
    <div class="grid md:grid-cols-2 gap-6 mb-6">
        @if($reportCard->teacher_notes)
        <div class="elite-card p-6">
            <h3 class="elite-h3 text-base ink-primary mb-2">Catatan Wali Kelas</h3>
            <p class="text-sm font-serif text-gray-700 leading-relaxed">{{ $reportCard->teacher_notes }}</p>
        </div>
        @endif
        @if($reportCard->extracurricular_notes)
        <div class="elite-card p-6">
            <h3 class="elite-h3 text-base ink-primary mb-2">Ekstrakurikuler</h3>
            <p class="text-sm font-serif text-gray-700 leading-relaxed">{{ $reportCard->extracurricular_notes }}</p>
        </div>
        @endif
    </div>
    @endif

@elseif($selectedStudent && !$reportCard)
    <div class="elite-card p-10 text-center">
        <p class="text-gray-500 font-serif text-lg italic">Belum ada data raport untuk siswa dan semester yang dipilih.</p>
    </div>
@else
    <div class="elite-card p-10 text-center">
        <p class="text-gray-500 font-serif text-lg italic">Pilih siswa dan semester untuk melihat raport interaktif.</p>
    </div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if(isset($chartData) && count($chartData['labels']) > 0)
    new Chart(document.getElementById('subjectChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartData['labels']) !!},
            datasets: [
                {
                    label: 'Nilai Diperoleh',
                    data: {!! json_encode($chartData['obtained']) !!},
                    backgroundColor: 'rgba(184, 134, 11, 0.7)',
                    borderColor: '#b8860b',
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Nilai Maksimum',
                    data: {!! json_encode($chartData['total']) !!},
                    backgroundColor: 'rgba(11, 29, 58, 0.15)',
                    borderColor: 'rgba(11, 29, 58, 0.4)',
                    borderWidth: 1,
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            },
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12 } }
            }
        }
    });
    @endif

    @if(isset($attendanceData))
    new Chart(document.getElementById('attendanceChart'), {
        type: 'doughnut',
        data: {
            labels: ['Hadir', 'Izin', 'Sakit', 'Alpa', 'Terlambat'],
            datasets: [{
                data: [
                    {{ $attendanceData['hadir'] }},
                    {{ $attendanceData['izin'] }},
                    {{ $attendanceData['sakit'] }},
                    {{ $attendanceData['alpa'] }},
                    {{ $attendanceData['terlambat'] }},
                ],
                backgroundColor: [
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(234, 179, 8, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(249, 115, 22, 0.8)',
                ],
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });
    @endif

    @if(isset($reportCard) && $reportCard->competency_scores)
    (function() {
        var compData = {!! json_encode($reportCard->competency_scores) !!};
        var labels = Object.keys(compData);
        var values = Object.values(compData);
        new Chart(document.getElementById('competencyChart'), {
            type: 'radar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Kompetensi',
                    data: values,
                    backgroundColor: 'rgba(184, 134, 11, 0.2)',
                    borderColor: '#b8860b',
                    borderWidth: 2,
                    pointBackgroundColor: '#b8860b',
                }]
            },
            options: {
                responsive: true,
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { stepSize: 20 }
                    }
                },
                plugins: { legend: { display: false } }
            }
        });
    })();
    @endif

    @if(isset($progressData) && count($progressData['labels']) > 1)
    new Chart(document.getElementById('progressChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($progressData['labels']) !!},
            datasets: [{
                label: 'GPA',
                data: {!! json_encode($progressData['gpa']) !!},
                borderColor: '#0b1d3a',
                backgroundColor: 'rgba(11, 29, 58, 0.1)',
                borderWidth: 2.5,
                pointRadius: 5,
                pointBackgroundColor: '#b8860b',
                tension: 0.3,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, max: 4.0, ticks: { stepSize: 0.5 } }
            },
            plugins: { legend: { display: false } }
        }
    });
    @endif
});
</script>
@endpush

@stop
