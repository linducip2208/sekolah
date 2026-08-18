@extends('layouts.school-admin')
@section('title', 'Detail Risiko — ' . $student->user?->name)
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Predictive Analytics — Detail</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Detail Risiko: {{ $student->user?->name }}</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">{{ $student->classSection?->classRoom?->name }} — {{ $student->classSection?->name }} | {{ $student->admission_no }}</p>
</div>

<div class="grid lg:grid-cols-3 gap-6 mb-6">
    {{-- Risk Score Card --}}
    <div class="bg-white border border-rule p-6 text-center">
        <div class="text-xs uppercase tracking-wider text-gray-500 mb-2 font-semibold">Skor Risiko</div>
        <div class="text-5xl font-bold {{ $riskScore >= 70 ? 'text-red-600' : ($riskScore >= 50 ? 'text-orange-600' : ($riskScore >= 30 ? 'text-yellow-600' : 'text-green-600')) }}">{{ $riskScore }}</div>
        <div class="mt-2">
            <span class="text-[.65rem] uppercase px-3 py-1 rounded font-semibold
                {{ $riskLevel === 'critical' ? 'bg-red-100 text-red-700' : ($riskLevel === 'high' ? 'bg-orange-100 text-orange-700' : ($riskLevel === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700')) }}">
                Risiko {{ ucfirst($riskLevel) }}
            </span>
        </div>
    </div>

    {{-- Radar Chart --}}
    <div class="bg-white border border-rule p-6 lg:col-span-2">
        <h3 class="elite-kicker text-[.65rem] mb-3">Faktor Risiko</h3>
        <canvas id="radarChart" height="220"></canvas>
    </div>
</div>

{{-- Factors Detail --}}
<div class="grid md:grid-cols-2 gap-6">
    <div class="bg-white border border-rule p-6">
        <h3 class="elite-kicker text-[.65rem] mb-4">Rincian Skor</h3>
        <div class="space-y-4">
            @php
            $factorLabels = [
                'attendance' => ['Kehadiran', 'attendance_score', 'attendance_rate', '%'],
                'academic'   => ['Akademik', 'academic_score', 'avg_mark_pct', '%'],
                'discipline' => ['Disiplin', 'discipline_score', 'discipline_count', ' insiden'],
                'engagement' => ['Engagement', 'engagement_score', null, ''],
                'financial'  => ['Finansial', 'financial_score', 'overdue_invoices', ' tagihan'],
            ];
            @endphp
            @foreach($factorLabels as $key => [$label, $scoreKey, $dataKey, $unit])
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-xs font-serif">{{ $label }}</span>
                    <span class="text-xs font-semibold">{{ $factors[$scoreKey] }} <span class="text-gray-400">× {{ $weights[$key] }}%</span></span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="h-2 rounded-full {{ $factors[$scoreKey] >= 50 ? 'bg-red-500' : ($factors[$scoreKey] >= 30 ? 'bg-yellow-500' : 'bg-green-500') }}"
                         style="width: {{ min(100, $factors[$scoreKey]) }}%"></div>
                </div>
                @if($dataKey && isset($factors[$dataKey]))
                <div class="text-[.6rem] text-gray-400 mt-0.5">
                    @if($key === 'attendance')Tingkat kehadiran: {{ $factors[$dataKey] }}{{ $unit }}
                    @elseif($key === 'academic')Rata-rata nilai: {{ $factors[$dataKey] }}{{ $unit }}
                    @elseif($key === 'discipline'){{ $factors[$dataKey] }}{{ $unit }}
                    @elseif($key === 'financial'){{ $factors[$dataKey] }}{{ $unit }} tertunggak
                    @endif
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white border border-rule p-6">
        <h3 class="elite-kicker text-[.65rem] mb-4">Interpretasi</h3>
        <div class="space-y-3 text-xs font-serif leading-relaxed">
            @if($riskLevel === 'critical')
            <div class="bg-red-50 border border-red-200 rounded p-3">
                <strong class="text-red-700">⚠️ Risiko Kritis</strong><br>
                Siswa ini menunjukkan tanda-tanda kuat berisiko putus sekolah. Diperlukan intervensi segera dari wali kelas, konselor, dan pihak sekolah.
            </div>
            @elseif($riskLevel === 'high')
            <div class="bg-orange-50 border border-orange-200 rounded p-3">
                <strong class="text-orange-700">⚠️ Risiko Tinggi</strong><br>
                Beberapa indikator menunjukkan penurunan signifikan. Perlu perhatian khusus dan pemantauan rutin.
            </div>
            @elseif($riskLevel === 'medium')
            <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                <strong class="text-yellow-700">📋 Risiko Sedang</strong><br>
                Ada beberapa area yang perlu diperhatikan. Pencegahan dini direkomendasikan.
            </div>
            @else
            <div class="bg-green-50 border border-green-200 rounded p-3">
                <strong class="text-green-700">✅ Risiko Rendah</strong><br>
                Siswa dalam kondisi baik. Pertahankan komunikasi dengan orang tua.
            </div>
            @endif

            <div class="mt-4">
                <h4 class="font-semibold mb-2">Bobot Analisis:</h4>
                <ul class="space-y-1">
                    @foreach($weights as $key => $weight)
                    <li class="flex justify-between">
                        <span>{{ ucfirst($key) }}</span>
                        <span class="font-mono">{{ $weight }}%</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="mt-6">
    <a href="{{ route('admin.analytics.predictive.index') }}" class="btn-elite" style="padding:.5rem 1.5rem;font-size:.65rem;">← Kembali ke Daftar</a>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('radarChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: ['Kehadiran', 'Akademik', 'Disiplin', 'Engagement', 'Finansial'],
                datasets: [{
                    label: 'Skor Risiko',
                    data: [
                        {{ $factors['attendance_score'] }},
                        {{ $factors['academic_score'] }},
                        {{ $factors['discipline_score'] }},
                        {{ $factors['engagement_score'] }},
                        {{ $factors['financial_score'] }}
                    ],
                    backgroundColor: 'rgba(239, 68, 68, 0.15)',
                    borderColor: 'rgba(239, 68, 68, 0.8)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(239, 68, 68, 1)',
                }]
            },
            options: {
                responsive: true,
                scales: {
                    r: { beginAtZero: true, max: 100, ticks: { stepSize: 25, font: { size: 10 } } }
                },
                plugins: { legend: { display: false } }
            }
        });
    }
});
</script>
@endpush
