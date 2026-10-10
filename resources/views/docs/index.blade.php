@extends('layouts.docs-tabler')

@section('title', 'Panduan Penggunaan')
@section('description', 'Buku panduan resmi ' . ($platform['app_name'] ?? 'Sikad Pro') . ' — disusun untuk setiap peran: administrator, orang tua, guru, siswa, dan operator platform.')

@section('content')
{{-- Hero with product screenshot --}}
<div class="docs-hero">
    <div class="container-xl py-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-6 text-center text-lg-start">
                <div class="page-pretitle mb-2"><i class="ti ti-book-2 me-1"></i> Liber Manualis</div>
                <h1 class="page-title display-5 fw-bold mb-3">Buku Panduan Resmi</h1>
                <p class="lead mb-4" style="max-width: 34rem;">
                    Setiap peran di institusi memiliki bab tersendiri. Pilih bab Anda untuk mempelajari
                    cara kerja sistem dari fondasi hingga penggunaan harian.
                </p>
                <div class="btn-list justify-content-center justify-content-lg-start">
                    <a href="#bab" class="btn btn-light btn-lg">
                        <i class="ti ti-book me-1"></i> Jelajahi Bab
                    </a>
                    <a href="/admin/login" class="btn btn-outline-light btn-lg">
                        <i class="ti ti-login me-1"></i> Coba Demo
                    </a>
                </div>
                <div class="d-flex gap-4 mt-4 justify-content-center justify-content-lg-start">
                    <div>
                        <div class="h2 text-white mb-0">47</div>
                        <div class="text-white-50 small">Modul ERP</div>
                    </div>
                    <div>
                        <div class="h2 text-white mb-0">6</div>
                        <div class="text-white-50 small">Bab Peran</div>
                    </div>
                    <div>
                        <div class="h2 text-white mb-0">142</div>
                        <div class="text-white-50 small">Menu Fitur</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 position-relative">
                <div class="browser-frame">
                    <div class="browser-bar"><i></i><i></i><i></i>
                        <span class="browser-url">app.sekolah.id/admin/dashboard</span>
                    </div>
                    <img src="{{ asset('marketing/screens/07-dashboard.png') }}" alt="Dashboard Sikad Pro" class="w-100" loading="eager">
                </div>
                <span class="badge bg-success docs-float-badge p-2" style="top: 8%; right: -0.5rem;">
                    <i class="ti ti-check me-1"></i> Realtime
                </span>
                <span class="badge bg-warning docs-float-badge p-2" style="bottom: 10%; left: -0.5rem;">
                    <i class="ti ti-device-mobile me-1"></i> Mobile-ready
                </span>
            </div>
        </div>
    </div>
</div>

<div class="page-body" id="bab">
    <div class="container-xl">
        <div class="row mb-4">
            <div class="col">
                <div class="page-pretitle">Capitula</div>
                <h2 class="page-title">Pilih Bab Sesuai Peran Anda</h2>
            </div>
        </div>
        <div class="row row-cards">
            @php $romans = ['I','II','III','IV','V','VI','VII','VIII','IX','X']; @endphp
            @foreach($roles as $i => $r)
                <div class="col-sm-6 col-lg-4">
                    <a href="{{ url('/docs/' . $r['slug']) }}" class="card card-link docs-chapter-card h-100">
                        <div class="img-responsive img-responsive-16x9 card-img-top border-0">
                            <img src="{{ asset($r['cover']) }}" alt="{{ strip_tags($r['title']) }}" loading="lazy">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-baseline justify-content-between mb-2">
                                <span class="stamp stamp-md bg-primary-lt text-primary">{{ $romans[$i] ?? ($i + 1) }}</span>
                                <span class="page-pretitle mb-0">{{ $r['kicker'] }}</span>
                            </div>
                            <h3 class="card-title h4">{!! $r['title'] !!}</h3>
                            <p class="text-secondary flex-grow-1">{{ $r['lead'] }}</p>
                            <div class="mt-3 text-primary fw-bold small text-uppercase">
                                Baca Bab <i class="ti ti-arrow-right ms-1"></i>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        {{-- Help CTA --}}
        <div class="row mt-5">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center py-5">
                        <div class="page-pretitle text-white-50 mb-2">Auxilium</div>
                        <h2 class="h1 text-white mb-3">Memerlukan Bantuan Lebih Lanjut?</h2>
                        <p class="text-white-50 mx-auto mb-4" style="max-width: 36rem;">
                            Tim kami siap memandu instalasi, migrasi data, dan pelatihan staf —
                            secara langsung maupun jarak jauh.
                        </p>
                        <div class="btn-list justify-content-center">
                            @if(!empty($platform['contact_phone']))
                                <a href="tel:{{ $platform['contact_phone'] }}" class="btn btn-light">
                                    <i class="ti ti-phone me-1"></i> {{ $platform['contact_phone'] }}
                                </a>
                            @endif
                            @if(!empty($platform['whatsapp_link']))
                                <a href="{{ $platform['whatsapp_link'] }}" target="_blank" rel="noopener" class="btn btn-outline-light">
                                    <i class="ti ti-brand-whatsapp me-1"></i> WhatsApp
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
