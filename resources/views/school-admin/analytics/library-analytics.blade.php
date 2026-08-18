@extends('layouts.school-admin')
@section('title', 'Library Analytics')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="space-y-6">
    <div>
        <div class="text-sm text-[var(--color-text-muted)]">Analytics</div>
        <h1 class="page-title mt-1">Library Analytics</h1>
        <p class="text-sm text-[var(--color-text-secondary)] mt-1">Statistik perpustakaan — buku, peminjaman, overdue, dan tren sirkulasi.</p>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="card card-pad">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--color-primary-soft);">
                    <svg class="w-5 h-5 text-[var(--color-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div>
                    <div class="text-xs text-[var(--color-text-muted)]">Total Buku</div>
                    <div class="text-xl font-extrabold">{{ number_format($totalBooks) }}</div>
                    <div class="text-xs text-[var(--color-text-muted)]">{{ number_format($totalTitles) }} judul</div>
                </div>
            </div>
        </div>
        <div class="card card-pad">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--color-success-soft);">
                    <svg class="w-5 h-5 text-[var(--color-success)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <div class="text-xs text-[var(--color-text-muted)]">Anggota Aktif</div>
                    <div class="text-xl font-extrabold">{{ number_format($totalMembers) }}</div>
                </div>
            </div>
        </div>
        <div class="card card-pad">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--color-danger-soft);">
                    <svg class="w-5 h-5 text-[var(--color-danger)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-xs text-[var(--color-text-muted)]">Overdue</div>
                    <div class="text-xl font-extrabold" style="color: var(--color-danger);">{{ $overdueCount }}</div>
                </div>
            </div>
        </div>
        <div class="card card-pad">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--color-warning-soft);">
                    <svg class="w-5 h-5 text-[var(--color-warning)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-xs text-[var(--color-text-muted)]">Denda Overdue</div>
                    <div class="text-xl font-extrabold">Rp {{ number_format($overdueValue / 100, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-4">
        {{-- Most Borrowed --}}
        <div class="card">
            <div class="px-5 py-4 border-b border-[var(--color-border)]">
                <h2 class="section-title">Buku Paling Dipinjam (Top 10)</h2>
            </div>
            @if($mostBorrowed->isEmpty())
                <div class="p-6"><x-feedback.empty-state icon="book" title="Belum ada data peminjaman" /></div>
            @else
                <div class="table-scroll">
                    <table class="table-elite">
                        <thead><tr><th>#</th><th>Judul</th><th>Pinjaman</th></tr></thead>
                        <tbody>
                            @foreach($mostBorrowed as $i => $b)
                            <tr>
                                <td class="text-[var(--color-text-muted)]">{{ $i + 1 }}</td>
                                <td class="font-semibold">{{ $b->book?->title ?? '-' }}</td>
                                <td><x-ui.badge variant="info">{{ $b->borrow_count }}</x-ui.badge></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Condition Distribution --}}
        <div class="card">
            <div class="px-5 py-4 border-b border-[var(--color-border)]">
                <h2 class="section-title">Distribusi Rak</h2>
            </div>
            <div class="p-4 space-y-2">
                @forelse($conditionDistribution as $c)
                <div class="flex items-center justify-between py-2 border-b border-[var(--color-border)] last:border-0">
                    <span class="text-sm">{{ $c->rack_location ?: 'Tanpa Rak' }}</span>
                    <x-ui.badge>{{ $c->total }}</x-ui.badge>
                </div>
                @empty
                    <div class="text-center text-[var(--color-text-muted)] py-6">Belum ada data</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Circulation Trend --}}
    <div class="card card-pad">
        <h2 class="section-title mb-4">Tren Sirkulasi Bulanan</h2>
        <canvas id="circulationChart" height="120"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('circulationChart'), {
        type: 'line',
        data: {
            labels: @json($circulationTrend['labels']),
            datasets: [{
                data: @json($circulationTrend['values']),
                borderColor: '#2563EB', backgroundColor: 'rgba(37,99,235,0.08)',
                fill: true, tension: 0.3
            }]
        }, options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
});
</script>
@endsection
