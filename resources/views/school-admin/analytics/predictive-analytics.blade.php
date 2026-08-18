@extends('layouts.school-admin')
@section('title', 'Prediksi Risiko Siswa')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Predictive Analytics</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Prediksi Risiko Siswa</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Analisis berbasis data untuk mengidentifikasi siswa berisiko putus sekolah.</p>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-sm text-green-800">{{ session('success') }}</div>
@endif

{{-- Distribution Cards --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white border border-rule p-4 text-center">
        <div class="text-2xl font-bold ink-primary">{{ $distribution['total'] }}</div>
        <div class="text-xs text-gray-500 font-serif">Total Siswa</div>
    </div>
    <div class="bg-white border border-rule p-4 text-center">
        <div class="text-2xl font-bold text-green-600">{{ $distribution['low'] }}</div>
        <div class="text-xs text-gray-500 font-serif">Risiko Rendah</div>
    </div>
    <div class="bg-white border border-rule p-4 text-center">
        <div class="text-2xl font-bold text-yellow-600">{{ $distribution['medium'] }}</div>
        <div class="text-xs text-gray-500 font-serif">Risiko Sedang</div>
    </div>
    <div class="bg-white border border-rule p-4 text-center">
        <div class="text-2xl font-bold text-orange-600">{{ $distribution['high'] }}</div>
        <div class="text-xs text-gray-500 font-serif">Risiko Tinggi</div>
    </div>
    <div class="bg-white border border-rule p-4 text-center">
        <div class="text-2xl font-bold text-red-600">{{ $distribution['critical'] }}</div>
        <div class="text-xs text-gray-500 font-serif">Kritis</div>
    </div>
</div>

{{-- Charts Row --}}
<div class="grid lg:grid-cols-3 gap-6 mb-6">
    {{-- Pie Chart --}}
    <div class="bg-white border border-rule p-6">
        <h3 class="elite-kicker text-[.65rem] mb-3">Distribusi Risiko</h3>
        <canvas id="riskPieChart" height="200"></canvas>
    </div>

    {{-- Filter --}}
    <div class="bg-white border border-rule p-6 lg:col-span-2">
        <h3 class="elite-kicker text-[.65rem] mb-3">Filter & Ringkasan</h3>
        <form method="GET" class="flex flex-wrap gap-3 mb-4">
            <select name="class_section_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
                <option value="">-- Semua Kelas --</option>
                @foreach($classSections as $cs)
                <option value="{{ $cs->id }}" {{ $classSectionId == $cs->id ? 'selected' : '' }}>{{ $cs->classRoom?->name }} — {{ $cs->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-elite" style="padding:.5rem 1rem;font-size:.65rem;">Filter</button>
        </form>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
            <div class="bg-gray-50 rounded p-3 text-center">
                <div class="font-bold text-lg">{{ $results->count() }}</div>
                <div class="text-gray-500">Ditampilkan</div>
            </div>
            <div class="bg-red-50 rounded p-3 text-center">
                <div class="font-bold text-lg text-red-600">{{ $results->filter(fn($r) => $r['risk_score'] >= 50)->count() }}</div>
                <div class="text-gray-500">Skor ≥ 50</div>
            </div>
            <div class="bg-yellow-50 rounded p-3 text-center">
                <div class="font-bold text-lg text-yellow-600">{{ $results->filter(fn($r) => $r['risk_score'] >= 30 && $r['risk_score'] < 50)->count() }}</div>
                <div class="text-gray-500">Skor 30-49</div>
            </div>
            <div class="bg-green-50 rounded p-3 text-center">
                <div class="font-bold text-lg text-green-600">{{ $results->filter(fn($r) => $r['risk_score'] < 30)->count() }}</div>
                <div class="text-gray-500">Skor &lt; 30</div>
            </div>
        </div>
    </div>
</div>

{{-- High Risk Students Table --}}
<div class="bg-white border border-rule overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-rule">
        <h3 class="elite-kicker text-[.65rem]">Siswa Berisiko Tinggi</h3>
        <p class="text-xs text-gray-500 font-serif mt-1">Daftar siswa dengan skor risiko ≥ 30, diurutkan dari yang tertinggi.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-4 py-3 text-left text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">#</th>
                    <th class="px-4 py-3 text-left text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">Siswa</th>
                    <th class="px-4 py-3 text-left text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">No. Induk</th>
                    <th class="px-4 py-3 text-center text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">Skor</th>
                    <th class="px-4 py-3 text-center text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">Level</th>
                    <th class="px-4 py-3 text-center text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">Kehadiran</th>
                    <th class="px-4 py-3 text-center text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">Akademik</th>
                    <th class="px-4 py-3 text-center text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">Disiplin</th>
                    <th class="px-4 py-3 text-center text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-rule">
                @forelse($highRisk as $i => $row)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-4 py-3 text-xs text-gray-400">{{ $i + 1 }}</td>
                    <td class="px-4 py-3 font-serif text-sm">{{ $row['student_name'] }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500 font-mono">{{ $row['admission_no'] }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full text-xs font-bold
                            {{ $row['risk_score'] >= 70 ? 'bg-red-100 text-red-700' : ($row['risk_score'] >= 50 ? 'bg-orange-100 text-orange-700' : ($row['risk_score'] >= 30 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700')) }}">
                            {{ $row['risk_score'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-[.6rem] uppercase px-2 py-1 rounded font-semibold
                            {{ $row['risk_level'] === 'critical' ? 'bg-red-100 text-red-700' : ($row['risk_level'] === 'high' ? 'bg-orange-100 text-orange-700' : ($row['risk_level'] === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700')) }}">
                            {{ $row['risk_level'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-xs">{{ $row['risk_factors']['attendance_rate'] }}%</td>
                    <td class="px-4 py-3 text-center text-xs">{{ $row['risk_factors']['avg_mark_pct'] }}%</td>
                    <td class="px-4 py-3 text-center text-xs">{{ $row['risk_factors']['discipline_count'] }}</td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('admin.analytics.predictive.detail', $row['student_id']) }}" class="text-[var(--c-primary)] text-xs hover:underline">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-8 text-center text-gray-400 font-serif text-sm">Tidak ada data risiko siswa.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('riskPieChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Rendah', 'Sedang', 'Tinggi', 'Kritis'],
                datasets: [{
                    data: [{{ $distribution['low'] }}, {{ $distribution['medium'] }}, {{ $distribution['high'] }}, {{ $distribution['critical'] }}],
                    backgroundColor: ['#22c55e', '#eab308', '#f97316', '#ef4444'],
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 11 } } }
                }
            }
        });
    }
});
</script>
@endpush
