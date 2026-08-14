<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $platform['app_name'] . ' — Platform Manajemen Sekolah Terpadu')</title>
    <meta name="description" content="@yield('description', $platform['description'])">
    <link rel="canonical" href="{{ url('/') }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', $platform['app_name'])">
    <meta property="og:description" content="@yield('description', $platform['description'])">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ $platform['og_image_url'] ?? ($platform['hero_image_url'] ?? null) }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', $platform['app_name'])">
    <meta name="twitter:description" content="@yield('description', $platform['description'])">

    @php $favicon = $platform['favicon_url'] ?? null; @endphp
    @if($favicon)<link rel="icon" href="{{ $favicon }}">@endif

    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'SoftwareApplication',
                'name' => $platform['app_name'],
                'applicationCategory' => 'EducationalApplication',
                'operatingSystem' => 'Web, Android, iOS',
                'description' => $platform['description'],
                'url' => url('/'),
                'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'IDR'],
            ],
            [
                '@type' => 'Organization',
                'name' => $platform['app_name'],
                'url' => url('/'),
                'logo' => $platform['logo_url'] ?? null,
                'contactPoint' => [
                    '@type' => 'ContactPoint',
                    'telephone' => $platform['contact_phone'] ?? '',
                    'email' => $platform['contact_email'] ?? '',
                    'contactType' => 'sales',
                ],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    {{-- Theme font --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="{{ $theme['fonts']['url'] }}" rel="stylesheet">

    {{-- Tailwind (utility classes) + design tokens --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            @foreach($theme['vars'] as $var => $val){{ $var }}: {{ $val }};@endforeach
            --lp-font-body: {{ $theme['fonts']['body'] }};
            --lp-font-display: {{ $theme['fonts']['display'] }};
        }
    </style>
    @vite(['resources/css/landing.css'])

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('head')
</head>
<body class="antialiased">

<x-landing.skip-link />

<x-landing.navbar :theme="$theme" />

@yield('content')

<x-landing.footer :theme="$theme" />

@include('landing._popup')

<script>
(function () {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('.reveal').forEach(function (el) { observer.observe(el); });
})();
</script>

@stack('scripts')
</body>
</html>
