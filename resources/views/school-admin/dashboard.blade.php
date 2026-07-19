@extends('layouts.school-admin')
@section('title', 'Cathedra Magistri')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-10">
    <div class="elite-kicker mb-2">{{ now()->translatedFormat('l, d F Y') }}</div>
    <h1 class="elite-h1 text-4xl ink-primary mb-3">Selamat datang, {{ auth()->user()->name }}</h1>
    <div class="elite-rule mb-3"></div>
    <p class="font-serif text-lg" style="color: var(--c-muted);">Panel administrasi {{ $branding['display_name'] ?? $platform['app_name'] }}. Semoga hari ini penuh capaian.</p>
</div>

@php
    $modules = [
        // [title, desc, route, group]
        ['PPDB',                  'Periode pendaftaran murid baru online.',           'admin.ppdb.dashboard',          'Siswa'],
        ['UKS / Klinik',          'Catatan medis siswa, kunjungan, vaksinasi.',       'admin.medical.dashboard',       'Siswa'],
        ['BP / BK Konseling',     'Sesi konseling, laporan bullying.',                'admin.counseling.dashboard',    'Siswa'],
        ['Disiplin Siswa',        'Pelanggaran, poin, tindakan.',                     'admin.discipline.dashboard',    'Siswa'],
        ['Transportasi',          'Bus sekolah, rute, ID gate.',                      'admin.transport.dashboard',     'Siswa'],
        ['Lesson Plan / RPP',     'Rencana pembelajaran per pertemuan.',              'admin.lesson-plan.dashboard',   'Pengajaran'],
        ['Live Class',            'Zoom, Google Meet, Jitsi integration.',            'admin.live-class.dashboard',    'Pengajaran'],
        ['AI Assistant',          'AI provider untuk auto-summary, generate soal.',   'admin.ai.dashboard',            'Pengajaran'],
        ['Kantin Cashless',       'Menu, dompet digital, top-up.',                    'admin.canteen.dashboard',       'Pengajaran'],
        ['Pesantren / Madrasah',  'Hafalan, ibadah, kitab kuning.',                   'admin.religious.dashboard',     'Pengajaran'],
        ['Event Sekolah',         'Acara, RSVP, ticket.',                             'admin.events.dashboard',        'Engagement'],
        ['Donasi / Fundraising',  'Campaign, donatur, laporan.',                      'admin.donations.dashboard',     'Engagement'],
        ['Prestasi Siswa',        'Tracker juara, badge digital.',                    'admin.achievements.dashboard',  'Engagement'],
        ['Beasiswa',              'Program & aplikasi beasiswa.',                     'admin.scholarship.dashboard',   'Engagement'],
        ['Visitor Management',    'Tamu, blacklist, log gerbang.',                    'admin.visitors.dashboard',      'Operasional'],
        ['Inventaris / Aset',     'Barang, peminjaman, maintenance.',                 'admin.inventory.dashboard',     'Operasional'],
        ['Sinkronisasi Dapodik',  'Sync data ke Dapodik Kemendikbud.',                'admin.dapodik.dashboard',       'Operasional'],
        ['Learning Analytics',    'Risk score siswa, pola belajar.',                  'admin.analytics.dashboard',     'Operasional'],
        ['Provider Pembayaran',   'Gateway SPP (Midtrans/Tripay/dll.).',              'admin.payment.providers.index', 'Keuangan'],
        ['Metode Pembayaran',     'VA, QRIS, e-wallet, manual transfer.',             'admin.payment.methods.index',   'Keuangan'],
        ['Branding & Logo',       'Warna, logo, splash screen mobile.',               'admin.branding.show',           'Tampilan'],
    ];
    $grouped = collect($modules)->groupBy(3);
@endphp

@foreach($grouped as $groupName => $items)
    <div class="mb-10">
        <div class="flex items-baseline gap-3 mb-4">
            <span class="font-display text-xl ink-accent">§</span>
            <h2 class="elite-h2 text-2xl ink-primary">{{ $groupName }}</h2>
            <div class="elite-kicker text-[.6rem] ml-2" style="color: var(--c-muted);">{{ $items->count() }} modul</div>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($items as $item)
                @php
                    [$title, $desc, $route] = $item;
                    try { $url = route($route); } catch (\Throwable) { $url = '#'; }
                @endphp
                <a href="{{ $url }}" class="elite-card p-5 group block">
                    <h3 class="elite-h3 text-base ink-primary mb-2 leading-tight">{{ $title }}</h3>
                    <div class="w-8 h-px bg-[var(--c-accent)] mb-2 group-hover:w-14 transition-all"></div>
                    <p class="font-serif text-xs leading-relaxed text-gray-600">{{ $desc }}</p>
                </a>
            @endforeach
        </div>
    </div>
@endforeach

<div class="mt-12 deco-frame">
    <div class="bg-white p-8">
        <div class="elite-kicker mb-2">Memerlukan Bantuan?</div>
        <h3 class="elite-h2 text-2xl ink-primary mb-2">Konsultasikan Buku Panduan</h3>
        <div class="elite-rule mb-4"></div>
        <p class="font-serif text-base text-gray-700 mb-4">Setiap fitur dijelaskan rinci di buku panduan resmi — tutorial step-by-step untuk semua peran.</p>
        <a href="/docs/admin" class="btn-elite-ghost">Buka Buku Panduan</a>
    </div>
</div>

@endsection
