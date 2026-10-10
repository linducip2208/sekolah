@extends('layouts.docs-tabler')

@section('title', 'Panduan Penggunaan')
@section('description', 'Buku panduan resmi ' . ($platform['app_name'] ?? 'Sikad Pro') . ' — disusun untuk setiap peran: administrator, orang tua, guru, siswa, dan operator platform.')

@section('content')
{{-- Hero --}}
<div class="docs-hero">
    <div class="container-xl py-5 text-center">
        <div class="page-pretitle mb-2"><i class="ti ti-book-2 me-1"></i> Liber Manualis</div>
        <h1 class="page-title display-5 fw-bold mb-3">Buku Panduan Resmi</h1>
        <p class="mw-100 mx-auto lead" style="max-width: 42rem;">
            Setiap peran di institusi memiliki bab tersendiri. Pilih bab Anda untuk mempelajari
            cara kerja sistem dari fondasi hingga penggunaan harian.
        </p>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            @php $romans = ['I','II','III','IV','V','VI','VII','VIII','IX','X']; @endphp
            @foreach($roles as $i => $r)
                <div class="col-sm-6 col-lg-4">
                    <a href="{{ url('/docs/' . $r['slug']) }}" class="card card-link docs-chapter-card h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-baseline justify-content-between mb-2">
                                <span class="stamp stamp-md bg-primary-lt text-primary">{{ $romans[$i] ?? ($i + 1) }}</span>
                                <span class="page-pretitle mb-0">Capitulum</span>
                            </div>
                            <div class="text-secondary text-uppercase small fw-bold mb-1">{{ $r['kicker'] }}</div>
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
