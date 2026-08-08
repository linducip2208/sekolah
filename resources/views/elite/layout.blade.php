@php $p = $platform ?? app(\App\Services\PlatformSettingsService::class)->all(); @endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $p['app_name'] ?? 'Sikad Pro') — {{ $p['app_name'] ?? 'Sikad Pro' }}</title>
    <meta name="description" content="@yield('description', $p['description'] ?? '')">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:title" content="@yield('title', $p['app_name'] ?? '')">
    <meta property="og:description" content="@yield('description', $p['description'] ?? '')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    @if(!empty($p['og_image_url']))<meta property="og:image" content="{{ $p['og_image_url'] }}">@endif
    <meta name="twitter:card" content="summary_large_image">

    @stack('jsonld')

    @include('elite.partials.head')
    @stack('head')
</head>
<body class="paper">
    @yield('header')
    @yield('content')
    @include('elite.partials.footer')
    @include('elite.partials.popup')
    @stack('scripts')
</body>
</html>
