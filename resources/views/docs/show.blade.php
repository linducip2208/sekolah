@extends('layouts.docs-tabler')

@section('title', strip_tags($title) . ' — Panduan')
@section('description', $lead)

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">{{ $kicker }}</div>
                <h1 class="page-title">{!! $title !!}</h1>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ $login_url }}" class="btn btn-primary">
                        <i class="ti ti-login me-1"></i> {{ $login_label }}
                    </a>
                    @if(!empty($platform['whatsapp_link']))
                        <a href="{{ $platform['whatsapp_link'] }}" target="_blank" rel="noopener" class="btn btn-outline-success">
                            <i class="ti ti-brand-whatsapp me-1"></i> Tanya via WhatsApp
                        </a>
                    @endif
                </div>
            </div>
        </div>
        <ol class="breadcrumb breadcrumb-arrows mt-3" aria-label="breadcrumbs">
            <li class="breadcrumb-item"><a href="/">Beranda</a></li>
            <li class="breadcrumb-item"><a href="/docs">Panduan</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $kicker }}</li>
        </ol>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row">
            {{-- Sidebar --}}
            <div class="col-lg-3 d-print-none mb-4">
                <div class="card sticky-top" style="top: 1rem;">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-list me-1"></i> Bab Lain</h3>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($roles as $r)
                            <a href="{{ url('/docs/' . $r['slug']) }}"
                               class="list-group-item list-group-item-action {{ $r['slug'] === $role ? 'active' : '' }}">
                                {!! $r['title'] !!}
                            </a>
                        @endforeach
                    </div>
                    <div class="card-footer">
                        <a href="{{ $login_url }}" class="btn btn-primary w-100">
                            <i class="ti ti-bolt me-1"></i> Tindakan Cepat
                        </a>
                    </div>
                </div>
            </div>

            {{-- Main content --}}
            <div class="col-lg-9 docs-content">
                {{-- Cover screenshot --}}
                <div class="browser-frame mb-4">
                    <div class="browser-bar"><i></i><i></i><i></i>
                        <span class="browser-url">{{ $login_url }}</span>
                    </div>
                    <img src="{{ asset($showcase['cover']) }}" alt="{{ strip_tags($title) }}" class="w-100" loading="eager">
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <p class="lead text-secondary mb-0">{{ $lead }}</p>
                    </div>
                </div>

                @if(!empty($credentials))
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-key me-1"></i> Kredensial Login Default</h3>
                        </div>
                        <div class="card-body">
                            @if(!empty($credentials['note']))
                                <p class="text-secondary">{{ $credentials['note'] }}</p>
                            @endif

                            @if(!empty($credentials['accounts']))
                                <div class="table-responsive">
                                    <table class="table table-vcenter card-table">
                                        <thead>
                                            <tr>
                                                <th>Email</th>
                                                <th>Password</th>
                                                <th>Konteks</th>
                                                <th>Role</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($credentials['accounts'] as $acc)
                                                <tr>
                                                    <td class="fw-bold">{{ $acc['email'] }}</td>
                                                    <td><code>{{ $acc['password'] }}</code></td>
                                                    <td class="text-secondary">{{ $acc['school'] }}</td>
                                                    <td><span class="badge bg-blue-lt">{{ $acc['role'] }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="alert alert-danger mt-3" role="alert">
                                    <div class="d-flex">
                                        <div><i class="ti ti-alert-triangle alert-icon"></i></div>
                                        <div>
                                            <h4 class="alert-title">Peringatan keamanan</h4>
                                            <div class="text-secondary">
                                                Akun-akun di atas dibuat oleh database seeder untuk keperluan demo dan development.
                                                Sebelum deploy ke production, ganti seluruh password (lewat halaman profil masing-masing user)
                                                atau jalankan ulang seeder versi production yang aman.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <p class="text-secondary mb-0">
                                    Tidak ada akun demo default untuk peran ini. Akun dibuatkan oleh administrator/TU sekolah.
                                </p>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="accordion" id="docs-sections">
                    @foreach($sections as $idx => $section)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="docs-h-{{ $idx }}">
                                <button class="accordion-button {{ $idx > 0 ? 'collapsed' : '' }}" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#docs-c-{{ $idx }}"
                                        aria-expanded="{{ $idx === 0 ? 'true' : 'false' }}" aria-controls="docs-c-{{ $idx }}">
                                    <span class="badge bg-primary-lt text-primary me-2">§ {{ $idx + 1 }}</span>
                                    {{ $section['title'] }}
                                </button>
                            </h2>
                            <div id="docs-c-{{ $idx }}" class="accordion-collapse collapse {{ $idx === 0 ? 'show' : '' }}"
                                 aria-labelledby="docs-h-{{ $idx }}" data-bs-parent="#docs-sections">
                                <div class="accordion-body">
                                    <ul class="steps steps-vertical">
                                        @foreach($section['items'] as $itemIdx => $item)
                                            <li class="step-item">
                                                <div class="h4 m-0">{{ str_pad($itemIdx + 1, 2, '0', STR_PAD_LEFT) }}</div>
                                                <div class="step-item-description">
                                                    @php
                                                        $text = e($item);
                                                        $text = preg_replace('#(/[a-zA-Z0-9\-\/_:.]+)#', '<code>$1</code>', $text);
                                                    @endphp
                                                    {!! $text !!}
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-center text-secondary mt-5">
                    <div class="page-pretitle">— {{ $platform['app_name'] ?? 'Sikad Pro' }}</div>
                </div>

                {{-- Screenshot gallery --}}
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="ti ti-photo me-1"></i> Galeri Tampilan</h3>
                        <div class="card-subtitle">Cuplikan layar asli aplikasi untuk peran ini.</div>
                    </div>
                    <div class="card-body">
                        <div class="docs-gallery">
                            @foreach($showcase['gallery'] as $shot)
                                <div class="browser-frame">
                                    <div class="browser-bar"><i></i><i></i><i></i></div>
                                    <img src="{{ asset($shot) }}" alt="Cuplikan {{ strip_tags($title) }}" loading="lazy">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
