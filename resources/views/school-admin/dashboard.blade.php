@extends('layouts.school-admin')
@section('title', 'Dashboard')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

@php
    $sid = auth()->user()->school_id;
    $school = \App\Models\School::find($sid);
    $safe = function (callable $fn, $default = 0) { try { return $fn(); } catch (\Throwable $e) { return $default; } };

    $stats = [
        'students' => $safe(fn () => \App\Models\Academic\Student::where('school_id', $sid)->count()),
        'staff'    => $safe(fn () => \App\Models\Academic\Staff::where('school_id', $sid)->count()),
        'invoices_unpaid' => $safe(fn () => \App\Models\Finance\FeeInvoice::where('school_id', $sid)->whereIn('status', ['unpaid', 'partial', 'overdue'])->count()),
        'outstanding' => $safe(fn () => (int) \App\Models\Finance\FeeInvoice::where('school_id', $sid)->whereIn('status', ['unpaid', 'partial', 'overdue'])->sum(\Illuminate\Support\Facades\DB::raw('amount - paid_amount'))),
        'notices' => $safe(fn () => \App\Models\Communication\Notice::where('school_id', $sid)->where('is_published', true)->count()),
        'ppdb'    => $safe(fn () => \App\Models\PPDB\PpdbApplication::where('school_id', $sid)->count()),
    ];

    $hour = (int) now()->format('G');
    $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
@endphp

<div class="space-y-6">

    {{-- Greeting --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div>
            <div class="text-sm text-[var(--color-text-muted)]">{{ now()->translatedFormat('l, d F Y') }}</div>
            <h1 class="page-title mt-1">{{ $greeting }}, {{ Str::before(auth()->user()->name, ' ') }} 👋</h1>
            <p class="text-sm text-[var(--color-text-secondary)] mt-1">
                Panel administrasi <strong>{{ $branding['display_name'] ?? $platform['app_name'] }}</strong>.
                Berikut ringkasan kegiatan hari ini.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-ui.button href="{{ route('admin.students.create') }}" icon="plus">Tambah Siswa</x-ui.button>
            <x-ui.button href="{{ route('admin.notices.create') }}" variant="secondary">Buat Pengumuman</x-ui.button>
        </div>
    </div>

    {{-- Stats overview --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @php
            $cards = [
                ['label' => 'Siswa',           'value' => number_format($stats['students'], 0, ',', '.'), 'icon' => '👨‍🎓', 'href' => route('admin.students.index')],
                ['label' => 'Staff & Guru',    'value' => number_format($stats['staff'], 0, ',', '.'), 'icon' => '👨‍🏫', 'href' => route('admin.staff.index')],
                ['label' => 'Tagihan Belum Lunas', 'value' => number_format($stats['invoices_unpaid'], 0, ',', '.'), 'icon' => '🧾', 'href' => route('admin.fee.invoices.index'), 'tone' => 'warning'],
                ['label' => 'Pendaftar PPDB',  'value' => number_format($stats['ppdb'], 0, ',', '.'), 'icon' => '🧒', 'href' => route('admin.ppdb.applications.index')],
            ];
        @endphp
        @foreach($cards as $c)
            <a href="{{ $c['href'] }}" class="card card-pad card-hover">
                <div class="flex items-start justify-between">
                    <span class="text-2xl" aria-hidden="true">{{ $c['icon'] }}</span>
                    @if(isset($c['tone']))
                        <span class="badge badge-warning">{{ $c['tone'] }}</span>
                    @endif
                </div>
                <div class="mt-3 text-2xl font-extrabold tracking-tight">{{ $c['value'] }}</div>
                <div class="text-sm text-[var(--color-text-secondary)]">{{ $c['label'] }}</div>
            </a>
        @endforeach
    </div>

    {{-- Outstanding + actions row --}}
    <div class="grid lg:grid-cols-3 gap-4">
        <div class="card card-pad lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h2 class="section-title">Perlu Tindakan</h2>
                <a href="{{ route('admin.fee.invoices.index') }}" class="text-sm text-[var(--color-primary)] hover:underline">Lihat semua</a>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <div class="text-sm text-[var(--color-text-secondary)]">Total piutang belum lunas</div>
                    <div class="text-3xl font-extrabold tracking-tight mt-1">{{ money($stats['outstanding'], $school) }}</div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-[var(--color-text-secondary)]">Tagihan</div>
                    <div class="text-3xl font-extrabold tracking-tight mt-1">{{ number_format($stats['invoices_unpaid'], 0, ',', '.') }}</div>
                </div>
            </div>
            @if($stats['invoices_unpaid'] > 0)
                <div class="mt-4 p-3 rounded-lg" style="background: var(--color-warning-soft);">
                    <p class="text-sm" style="color: #a16207;">Ada {{ $stats['invoices_unpaid'] }} tagihan yang menunggu pembayaran. Kirim pengingat untuk mempercepat pelunasan.</p>
                </div>
            @else
                <div class="mt-4 p-3 rounded-lg" style="background: var(--color-success-soft);">
                    <p class="text-sm" style="color: #15803d;">Semua tagihan telah lunas. Kerja bagus!</p>
                </div>
            @endif
        </div>

        <div class="card card-pad">
            <h2 class="section-title mb-4">Aksi Cepat</h2>
            <div class="space-y-1">
                @foreach([
                    ['admin.attendance.index', 'Catat Absensi', '📋'],
                    ['admin.timetable.index', 'Jadwal Pelajaran', '📅'],
                    ['admin.exams.index', 'Kelola Ujian', '📝'],
                    ['admin.library.books.index', 'Perpustakaan', '📚'],
                    ['admin.reports.builder.index', 'Buat Laporan', '📊'],
                    ['admin.branding.show', 'Branding Sekolah', '🎨'],
                ] as $qa)
                    <a href="{{ route($qa[0]) }}" class="dropdown-item rounded-lg"><span aria-hidden="true">{{ $qa[2] }}</span> {{ $qa[1] }}</a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Module grid --}}
    @php
        $modules = [
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
        <div>
            <h2 class="section-title mb-3">{{ $groupName }}</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach($items as $item)
                    @php
                        [$title, $desc, $route] = $item;
                        try { $url = route($route); } catch (\Throwable) { $url = '#'; }
                    @endphp
                    <a href="{{ $url }}" class="card card-pad card-hover block">
                        <h3 class="text-base font-semibold leading-tight">{{ $title }}</h3>
                        <p class="text-[13px] leading-relaxed mt-1" style="color: var(--color-text-secondary);">{{ $desc }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="card card-pad flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h3 class="font-semibold">Butuh bantuan?</h3>
            <p class="text-sm text-[var(--color-text-secondary)]">Buku panduan resmi berisi tutorial step-by-step untuk setiap peran.</p>
        </div>
        <x-ui.button href="/docs/admin" variant="secondary">Buka Buku Panduan</x-ui.button>
    </div>

</div>

@endsection
