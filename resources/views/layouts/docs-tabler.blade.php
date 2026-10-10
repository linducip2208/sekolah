<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Panduan') — {{ $platform['app_name'] ?? 'Sikad Pro' }}</title>
    <meta name="description" content="@yield('description', 'Buku panduan resmi ' . ($platform['app_name'] ?? 'Sikad Pro'))">
    @vite(['resources/css/docs-tabler.css', 'resources/js/docs-tabler.js'])
</head>
<body>
<div class="page">
    {{-- Top navbar --}}
    <header class="navbar navbar-expand-md d-print-none">
        <div class="container-xl">
            <a href="/" class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                <span class="avatar avatar-sm bg-primary text-white rounded me-2">{{ Str::upper(Str::substr($platform['app_name'] ?? 'S', 0, 1)) }}</span>
                {{ $platform['app_name'] ?? 'Sikad Pro' }}
            </a>
            <div class="navbar-nav flex-row order-md-last ms-auto">
                <div class="nav-item d-none d-md-flex me-3">
                    <div class="btn-list">
                        <a href="/docs" class="btn btn-outline-primary">
                            <i class="ti ti-book me-1"></i> Panduan
                        </a>
                        <a href="/admin/login" class="btn btn-primary">
                            <i class="ti ti-login me-1"></i> Masuk
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="page-wrapper">
        @yield('content')

        <footer class="footer footer-transparent d-print-none mt-5">
            <div class="container-xl">
                <div class="row text-center align-items-center">
                    <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                        <ul class="list-inline list-inline-dots mb-0">
                            <li class="list-inline-item">{{ $platform['app_name'] ?? 'Sikad Pro' }} — Buku Panduan</li>
                            <li class="list-inline-item"><a href="/docs" class="link-secondary">Semua Bab</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>
@stack('scripts')
</body>
</html>
