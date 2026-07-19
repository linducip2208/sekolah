<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $branding = isset($branding)
            ? $branding
            : (auth()->check() && auth()->user()->school_id
                ? app(\App\Services\Branding\BrandingService::class)->getForSchool(auth()->user()->school_id)
                : null);
        $displayName = $branding['display_name'] ?? ($platform['app_name'] ?? config('app.name', 'eSchool'));
        $logoPrimary = $branding['logos']['primary'] ?? ($platform['logo_url'] ?? null);
        $favicon = $branding['logos']['favicon'] ?? ($platform['favicon_url'] ?? null);
        $cacheVer = $branding['cache_version'] ?? 1;
    @endphp
    <title>@yield('title', 'Portal Familiae') — {{ $displayName }}</title>
    @if($favicon)<link rel="icon" href="{{ $favicon }}?v={{ $cacheVer }}">@endif
    @include('elite.partials.head')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('head')
</head>
<body class="paper">

<header class="bg-white border-b border-rule sticky top-0 z-30">
    <div class="max-w-6xl mx-auto px-3 sm:px-5 lg:px-6">
        <div class="hidden sm:flex items-center justify-between py-2.5 border-b border-rule/60">
            <div class="elite-kicker truncate" style="color: var(--c-muted);">{{ $displayName }} · Portal Familiae</div>
            <div class="elite-kicker hidden md:block" style="color: var(--c-muted);">{{ now()->translatedFormat('l, d F Y') }}</div>
        </div>
        <div class="flex items-center justify-between gap-3 py-3 sm:py-5">
            <a href="{{ route('portal.invoices') }}" class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                @if($logoPrimary)
                    <img src="{{ $logoPrimary }}?v={{ $cacheVer }}" class="h-9 sm:h-11 w-auto flex-shrink-0" alt="">
                @else
                    <div class="crest-mark flex-shrink-0">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 2 7l10 5 10-5-10-5zm0 7L2 14l10 5 10-5-10-5z"/></svg>
                    </div>
                @endif
                <div class="leading-tight min-w-0">
                    <div class="elite-h3 text-base sm:text-xl ink-primary truncate">{{ $displayName }}</div>
                    <div class="elite-kicker text-[.55rem] sm:text-[.6rem] mt-0.5 truncate" style="letter-spacing:.3em;">@yield('title', 'Portal Familiae')</div>
                </div>
            </a>
            <div class="flex items-center gap-2 sm:gap-4 flex-shrink-0">
                @auth
                    <div class="text-right hidden md:block">
                        <div class="font-serif text-base ink-primary leading-tight truncate max-w-[160px]">{{ auth()->user()?->name }}</div>
                        <div class="elite-kicker text-[.55rem]" style="color: var(--c-muted);">Pater/Mater Familias</div>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">@csrf
                        <button class="elite-kicker text-xs ink-secondary hover:ink-accent transition px-2 py-1.5" style="letter-spacing: .18em;">
                            <span class="hidden sm:inline">Keluar Sesi</span>
                            <span class="sm:hidden">↩</span>
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </div>
</header>

<main class="max-w-6xl mx-auto px-3 sm:px-5 lg:px-6 py-5 sm:py-8 lg:py-10">
    @if(session('success'))
        <div class="mb-5 px-5 py-3 bg-white border-l-4" style="border-color: var(--c-accent);">
            <span class="font-serif text-base ink-primary"><span class="ink-accent mr-2">❦</span>{{ session('success') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="mb-5 px-5 py-3 bg-white border-l-4 border-red-700">
            <ul class="list-disc list-inside font-serif text-base text-red-800">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif
    @yield('content')
</main>

<footer class="bg-primary text-white/85 mt-10 sm:mt-20" style="background: var(--c-primary);">
    <div class="max-w-6xl mx-auto px-3 sm:px-5 lg:px-6 py-6 sm:py-10 text-center">
        <div class="font-script italic text-base sm:text-xl mb-2" style="color: var(--c-accent);">"{{ $platform['motto_latin'] ?? 'Floreat Schola' }}"</div>
        <div class="elite-kicker text-[.55rem] sm:text-[.6rem] mb-3 sm:mb-4" style="color: rgba(255,255,255,.5);">{{ $platform['motto_translated'] ?? '' }}</div>
        <div class="text-[11px] sm:text-xs text-white/55" style="font-family:'Inter',sans-serif;">&copy; {{ now()->year }} {{ $displayName }} · {{ $platform['app_name'] ?? '' }}</div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
