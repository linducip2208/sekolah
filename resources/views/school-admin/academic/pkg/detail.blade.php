@extends('layouts.school-admin')
@section('title', 'Detail PKG')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Pengajaran</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Detail PKG</h1>
    <div class="elite-rule"></div>
</div>

<div class="grid md:grid-cols-3 gap-6 mb-6">
    <div class="elite-card p-5 md:col-span-2">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Informasi Penilaian</h3>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div><span class="elite-kicker text-[.55rem]">Guru Dinilai</span><br><span class="font-serif font-semibold">{{ $assessment->teacher?->name }}</span></div>
            <div><span class="elite-kicker text-[.55rem]">Penilai</span><br><span class="font-serif font-semibold">{{ $assessment->assessor?->name ?? '—' }}</span></div>
            <div><span class="elite-kicker text-[.55rem]">Tipe</span><br>{{ match($assessment->type){'self'=>'Self Assessment','peer'=>'Peer Review','supervisor'=>'Kepala Sekolah/Pengawas',default:$assessment->type} }}</div>
            <div><span class="elite-kicker text-[.55rem]">Semester</span><br>Smt {{ $assessment->semester }} · {{ $assessment->academicYear?->name }}</div>
            <div><span class="elite-kicker text-[.55rem]">Tanggal</span><br>{{ $assessment->assessment_date?->format('d M Y') }}</div>
            <div><span class="elite-kicker text-[.55rem]">Status</span><br>{{ match($assessment->status){'draft'=>'Draft','submitted'=>'Terkirim','verified'=>'Terverifikasi',default:$assessment->status} }}</div>
        </div>
    </div>

    <div class="elite-card p-5 flex flex-col items-center justify-center text-center">
        <div class="elite-kicker text-[.55rem] mb-2">Skor Akhir</div>
        <div class="font-display text-5xl font-bold ink-accent mb-2">{{ $assessment->final_score ?? '—' }}</div>
        @php
            $recLabel = app(\App\Services\PkgService::class)->getRecommendationLabel($assessment->recommendation);
            $recColor = app(\App\Services\PkgService::class)->getRecommendationColor($assessment->recommendation);
        @endphp
        <div class="text-sm font-semibold px-3 py-1 rounded" style="background:{{ $recColor }}22;color:{{ $recColor }}">{{ $recLabel }}</div>
        @if($assessment->notes)<div class="mt-3 text-xs text-gray-500 italic font-serif">"{{ $assessment->notes }}"</div>@endif
    </div>
</div>

{{-- Radar Chart --}}
@if($assessment->scores->isNotEmpty())
<div class="elite-card p-5 mb-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Radar Kompetensi</h3>
    <div class="max-w-xl mx-auto"><canvas id="radarChart"></canvas></div>
</div>
@endif

{{-- Score Detail --}}
<div class="elite-card p-5 mb-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Rincian Skor per Kompetensi</h3>
    <div class="table-scroll"><table class="w-full text-sm table-elite">
    <thead><tr>
    <th>Kode</th><th>Kompetensi</th><th>Tipe</th><th>Bobot</th><th class="text-right">Skor</th><th>Bukti</th>
    </tr></thead><tbody>
    @foreach($competencies as $comp)
    <tr>
    <td class="font-mono text-xs">{{ $comp->code }}</td>
    <td class="font-serif text-sm">{{ $comp->name }}</td>
    <td class="text-xs">{{ $comp->competency_type }}</td>
    <td class="text-xs">{{ $comp->weight }}</td>
    <td class="text-right font-mono font-bold">{{ $scoreMap[$comp->id] ?? '—' }}</td>
    <td class="text-xs text-gray-600">{{ $assessment->scores->where('pkg_competency_id', $comp->id)->first()?->evidence_notes ?? '—' }}</td>
    </tr>
    @endforeach
    </tbody></table></div>
</div>

{{-- Observations --}}
@if($assessment->observations->isNotEmpty())
<div class="elite-card p-5 mb-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Observasi Kelas</h3>
    @foreach($assessment->observations as $obs)
    <div class="grid grid-cols-2 gap-3 text-sm border-b border-rule pb-3 mb-3 last:border-0 last:pb-0 last:mb-0">
        <div><span class="elite-kicker text-[.55rem]">Tanggal</span><br>{{ $obs->observation_date?->format('d M Y') }}</div>
        <div><span class="elite-kicker text-[.55rem]">Kelas</span><br>{{ $obs->classSection?->classRoom?->name }} {{ $obs->classSection?->section?->name }}</div>
        <div><span class="elite-kicker text-[.55rem]">Suasana</span><br>{{ $obs->class_atmosphere }}</div>
        <div><span class="elite-kicker text-[.55rem]">Keterlibatan</span><br>{{ $obs->student_engagement }}</div>
        @if($obs->observation_notes)<div class="col-span-2"><span class="elite-kicker text-[.55rem]">Catatan</span><br><span class="font-serif italic text-sm">{{ $obs->observation_notes }}</span></div>@endif
    </div>
    @endforeach
</div>
@endif

<div class="flex gap-3">
    <a href="{{ route('admin.pkg.index') }}" class="btn-elite-ghost text-xs">← Kembali</a>
    <a href="{{ route('admin.pkg.export-pdf', $assessment) }}" class="btn-elite text-xs">↓ Unduh PDF</a>
    @if($assessment->status !== 'verified')
    <form method="POST" action="{{ route('admin.pkg.verify', $assessment) }}" class="inline">@csrf<button class="btn-elite-gold text-xs">Verifikasi</button></form>
    @endif
</div>
@endsection

@push('scripts')
@if($assessment->scores->isNotEmpty())
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('radarChart'), {
        type: 'radar',
        data: {
            labels: @json($assessment->scores->map(fn($s) => $s->competency?->code . ': ' . \Illuminate\Support\Str::limit($s->competency?->name, 20))),
            datasets: [{
                label: 'Skor',
                data: @json($assessment->scores->pluck('score')),
                borderColor: '#b8860b',
                backgroundColor: 'rgba(184,134,11,0.15)',
                pointBackgroundColor: '#b8860b',
            }]
        },
        options: {
            scales: {r: {beginAtZero: false, min: 0, max: 100, ticks: {stepSize: 20}}},
            plugins: {legend: {display: false}}
        }
    });
});
</script>
@endif
@endpush
