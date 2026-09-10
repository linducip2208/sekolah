@extends('layouts.school-admin')

@section('title', 'Dashboard Role')

@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@section('content')
@php
    $roleLabels = [
        'principal' => 'Kepala Sekolah', 'teacher' => 'Guru', 'finance' => 'Bendahara',
        'counselor' => 'Guru BK', 'hr' => 'HR', 'librarian' => 'Pustakawan',
        'ppdb_officer' => 'Petugas PPDB', 'nurse' => 'Petugas UKS', 'transport' => 'Admin Transportasi',
        'hostel' => 'Admin Asrama', 'procurement' => 'Admin Pengadaan', 'foundation' => 'Yayasan',
        'student' => 'Siswa', 'parent' => 'Orang Tua',
    ];
    $roleLabel = $roleLabels[$role] ?? ucfirst($role);
@endphp

<div class="space-y-6">

    <x-ui.page-header title="Pusat Kendali {{ $roleLabel }}" subtitle="Ringkasan KPI dan akses cepat yang relevan dengan peran Anda." :backHref="route('admin.dashboard')" backLabel="Dashboard Utama">
        <a href="{{ route('admin.my-work') }}" class="btn btn-secondary btn-sm">My Work</a>
        <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-search'))" class="btn btn-sm">Cari (⌘K)</button>
    </x-ui.page-header>

    {{-- ===== KPI utama ===== --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-{{ count($widgets) > 4 ? '5' : '4' }} gap-3 sm:gap-4">
        @foreach($widgets as $widget)
            <a href="{{ $widget['url'] ?? '#' }}" class="card card-pad card-hover block group">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[12px] font-medium text-[var(--color-text-secondary)] leading-snug min-h-[2rem]">{{ $widget['title'] }}</p>
                    <span class="h-8 w-8 rounded-lg flex items-center justify-center flex-shrink-0 transition-transform group-hover:scale-105"
                          style="background: var(--color-primary-soft); color: var(--color-primary);" aria-hidden="true">
                        @include('school-admin.partials.widget-icon', ['icon' => $widget['icon'] ?? 'school'])
                    </span>
                </div>
                <div class="mt-1 text-2xl font-extrabold tracking-tight tabular-nums" style="color: var(--color-text);">{{ $widget['value'] }}</div>
            </a>
        @endforeach
    </div>

    {{-- ===== Konteks khusus role ===== --}}
    @if($role === 'student')
        @if(!empty($upcomingExams) && count($upcomingExams))
            <div class="card">
                <div class="px-5 py-4 border-b border-[var(--color-border)] flex items-center justify-between">
                    <h2 class="section-title">Ujian Mendatang</h2>
                </div>
                <ul class="divide-y divide-[var(--color-border)]">
                    @foreach($upcomingExams as $exam)
                        <li class="flex items-center justify-between px-5 py-3.5">
                            <div>
                                <div class="text-sm font-semibold">{{ $exam->name ?? 'Ujian' }}</div>
                                <div class="text-xs text-[var(--color-text-muted)] mt-0.5">{{ \Carbon\Carbon::parse($exam->date)->translatedFormat('l, d F Y') }}</div>
                            </div>
                            <x-ui.badge variant="warning">Mendatang</x-ui.badge>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif

    @if($role === 'parent')
        @isset($children)
        <div class="card card-pad">
            <h2 class="section-title mb-4">Anak Anda</h2>
            @if($children->isEmpty())
                <x-feedback.empty-state icon="user" title="Belum ada data anak" description="Hubungi Tata Usaha sekolah untuk mengaitkan akun ini dengan data anak Anda." />
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($children as $child)
                        <a href="{{ route('portal.child', $child) }}" class="card card-pad card-hover block">
                            <div class="flex items-center gap-3">
                                <x-ui.avatar :name="$child->first_name ?? $child->name" />
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold truncate">{{ $child->first_name ?? $child->name }}</div>
                                    <div class="text-xs text-[var(--color-text-muted)]">{{ $child->classSection?->classRoom?->name ?? '-' }} {{ $child->classSection?->section?->name }}</div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
        @endisset
    @endif

    @if($role === 'librarian' && !empty($recentPayments) === false)
    @endif

    {{-- Bendahara: pembayaran terbaru --}}
    @if($role === 'finance' && !empty($recentPayments))
        <div class="card">
            <div class="px-5 py-4 border-b border-[var(--color-border)] flex items-center justify-between">
                <h2 class="section-title">Pembayaran Terbaru</h2>
                <a href="{{ route('admin.finance.reports.summary') }}" class="text-sm font-medium text-[var(--color-primary)] hover:underline">Laporan</a>
            </div>
            <div class="table-scroll">
                <table class="table-elite">
                    <thead><tr><th>Siswa</th><th>Status</th><th>Nominal</th></tr></thead>
                    <tbody>
                        @foreach($recentPayments as $inv)
                            <tr>
                                <td class="font-medium">{{ $inv->student?->user?->name ?? ('INV #' . $inv->id) }}</td>
                                <td><x-ui.status :status="$inv->status" /></td>
                                <td class="tabular-nums">Rp {{ number_format(($inv->amount - $inv->paid_amount) / 100, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
