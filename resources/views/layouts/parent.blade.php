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
        $displayName = $branding['display_name'] ?? ($platform['app_name'] ?? config('app.name', 'Sikad Pro'));
        $logoPrimary = $branding['logos']['primary'] ?? ($platform['logo_url'] ?? null);
        $favicon = $branding['logos']['favicon'] ?? ($platform['favicon_url'] ?? null);
        $cacheVer = $branding['cache_version'] ?? 1;
        // Halaman anak saat ini (untuk highlight switcher)
        $activeChildId = isset($student) && isset($student->id) ? $student->id : null;
        $isStudentPortal = request()->routeIs('student.*');
        $portalLabel = $isStudentPortal ? 'Portal Siswa' : 'Portal Orang Tua';
        $portalNav = [
            ['portal.dashboard', 'Beranda', 'M3 12l9-9 9 9M5 10v10h14V10'],
            ['portal.invoices', 'Tagihan', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['portal.conferences', 'Konferensi Guru', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
            ['forum.index', 'Komunitas', 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
            ['portal.committee', 'Komite Sekolah', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
        ];
    @endphp
    <title>@yield('title', $portalLabel) — {{ $displayName }}</title>
    @if($favicon)<link rel="icon" href="{{ $favicon }}?v={{ $cacheVer }}">@endif
    @include('elite.partials.head')

    {{-- Per-school white-label theme --}}
    @if(auth()->check() && auth()->user()->school_id)
        @php $gfu = $branding['font']['google_fonts_url'] ?? null; @endphp
        @if($gfu)<link rel="stylesheet" href="{{ $gfu }}">@endif
        <link rel="stylesheet" href="{{ route('branding.css', ['schoolId' => auth()->user()->school_id, 'v' => $cacheVer]) }}">
    @endif

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css'])
    <script>
        (function () {
            try {
                var m = localStorage.getItem('sikadpro:theme');
                var mode = m ? JSON.parse(m) : 'system';
                var dark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
            } catch (e) {}
        })();
    </script>
    @stack('head')
</head>
<body class="paper">

<script src="{{ Vite::asset('resources/js/app.js') }}"></script>
@include('offline-status')

<a href="#main-content" class="skip-link">Langsung ke konten</a>

<header class="sticky top-0 z-30 border-b backdrop-blur-md" style="background: color-mix(in srgb, var(--color-surface) 88%, transparent); border-color: var(--color-border);">
    <div class="max-w-6xl mx-auto px-3 sm:px-5 lg:px-6">
        <div class="flex items-center justify-between gap-3 py-3">
            <a href="{{ route('portal.dashboard') }}" class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1" aria-label="Beranda portal">
                @if($logoPrimary)
                    <img src="{{ $logoPrimary }}?v={{ $cacheVer }}" class="h-9 sm:h-10 w-auto flex-shrink-0 rounded-lg bg-white/80 p-0.5" alt="{{ $displayName }}">
                @else
                    <span class="h-9 w-9 sm:h-10 sm:w-10 rounded-xl flex items-center justify-center flex-shrink-0 text-white font-extrabold" style="background: var(--color-primary);" aria-hidden="true">{{ Str::upper(Str::substr($displayName, 0, 1)) }}</span>
                @endif
                <div class="leading-tight min-w-0 hidden min-[420px]:block">
                    <div class="text-sm sm:text-base font-bold truncate" style="color: var(--color-text);">{{ $displayName }}</div>
                    <div class="text-[10px] font-semibold uppercase tracking-widest" style="color: var(--color-text-muted);">{{ $portalLabel }}</div>
                </div>
            </a>

            <div class="flex items-center gap-1.5 sm:gap-2 flex-shrink-0">
                {{-- Children switcher --}}
                @auth
                    @if(isset($children) && $children->count() > 0)
                        <div x-data="{ open: false }" class="relative">
                            <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="true"
                                    class="flex items-center gap-2 pl-1.5 pr-2 py-1.5 rounded-xl border transition hover:bg-[var(--color-surface-hover)]"
                                    style="border-color: var(--color-border); background: var(--color-surface);"
                                    aria-label="Pilih anak">
                                <span class="avatar avatar-sm !w-7 !h-7 text-xs" aria-hidden="true">{{ Str::upper(Str::substr($children->firstWhere('id', $activeChildId)->user?->name ?? $children->first()->user?->name ?? '?', 0, 1)) }}</span>
                                <span class="text-xs font-semibold max-w-[7rem] truncate hidden sm:block">{{ ($children->firstWhere('id', $activeChildId)?->user?->name) ?? ($children->first()?->first_name ?? $children->first()->user?->name) }}</span>
                                <svg class="w-3.5 h-3.5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-cloak @click.outside="open = false"
                                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                 class="dropdown-panel right-0" style="min-width: 15rem;" role="menu">
                                <div class="dropdown-label">Anak Anda</div>
                                @foreach($children as $c)
                                    <a href="{{ route('portal.child', $c) }}" class="dropdown-item" role="menuitem">
                                        <span class="avatar avatar-sm" aria-hidden="true">{{ Str::upper(Str::substr($c->user?->name ?? '?', 0, 1)) }}</span>
                                        <span class="min-w-0">
                                            <span class="block text-sm font-medium truncate">{{ $c->user?->name }}</span>
                                            <span class="block text-[11px]" style="color: var(--color-text-muted);">{{ $c->classSection?->classRoom?->name }} {{ $c->classSection?->section?->name }}</span>
                                        </span>
                                        @if($c->id === $activeChildId)<x-ui.icon name="check" class="w-4 h-4 ms-auto text-[var(--color-primary)]" />@endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('toggle-theme'))"
                            class="btn-icon" aria-label="Ganti mode terang/gelap">
                        <svg class="w-5 h-5 theme-icon-sun" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <svg class="w-5 h-5 theme-icon-moon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    </button>

                    <form method="POST" action="{{ route('admin.logout') }}">@csrf
                        <button type="submit" class="btn-icon" aria-label="Keluar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                @endauth
            </div>
        </div>

        {{-- Portal nav — simple, bukan ERP (hanya portal orang tua; portal siswa punya nav sendiri) --}}
        @auth
        @if(!$isStudentPortal)
        <nav class="flex gap-0.5 overflow-x-auto pb-0.5" style="scrollbar-width: none; -webkit-overflow-scrolling: touch;" aria-label="Navigasi portal">
            @foreach($portalNav as [$rt, $label, $path])
                @php
                    $navUrl = rescue(fn () => route($rt), '#', false);
                    $isActive = request()->routeIs($rt) || ($rt === 'portal.dashboard' && request()->routeIs('portal.index'));
                @endphp
                <a href="{{ $navUrl }}" aria-current="{{ $isActive ? 'page' : false }}"
                   class="relative inline-flex items-center gap-1.5 whitespace-nowrap px-3 py-2.5 text-sm font-medium transition-colors min-h-[40px]"
                   style="{{ $isActive ? 'color: var(--color-primary);' : 'color: var(--color-text-secondary);' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $path }}"/></svg>
                    {{ $label }}
                    @if($rt === 'portal.invoices' && isset($outstanding) && $outstanding > 0)
                        <span class="inline-flex items-center justify-center min-w-[1.15rem] h-[1.15rem] px-1 text-[10px] font-bold text-white rounded-full" style="background: var(--color-danger);">!</span>
                    @endif
                    @if($isActive)
                        <span class="absolute inset-x-2 bottom-0 h-[2.5px] rounded-t-full" style="background: var(--color-primary);" aria-hidden="true"></span>
                    @endif
                </a>
            @endforeach
        </nav>
        @endif
        @endauth
    </div>
</header>

<main id="main-content" class="max-w-6xl mx-auto px-3 sm:px-5 lg:px-6 py-5 sm:py-8 lg:py-10">
    @if(session('success'))
        <x-ui.alert variant="success" dismissible class="mb-5">{{ session('success') }}</x-ui.alert>
    @endif
    @if(isset($errors) && $errors->any())
        <x-ui.alert variant="danger" class="mb-5">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </x-ui.alert>
    @endif
    @yield('content')
</main>

<footer class="mt-10 sm:mt-20 border-t" style="background: var(--color-background); border-color: var(--color-border);">
    <div class="max-w-6xl mx-auto px-3 sm:px-5 lg:px-6 py-6 sm:py-8 text-center">
        <div class="font-script italic text-base mb-1" style="color: var(--color-accent);">"{{ $platform['motto_latin'] ?? 'Floreat Schola' }}"</div>
        <div class="text-xs mb-3" style="color: var(--color-text-muted);">{{ $platform['motto_translated'] ?? '' }}</div>
        <div class="text-[11px]" style="color: var(--color-text-muted);">&copy; {{ now()->year }} {{ $displayName }} · {{ $platform['app_name'] ?? 'Sikad Pro' }}</div>
    </div>
</footer>

<x-overlays.toast />
<x-overlays.confirm-dialog />

<style>
    /* Theme icon swap */
    html[data-theme="light"] .theme-icon-moon { display: none; }
    html[data-theme="dark"] .theme-icon-sun { display: none; }
</style>
@stack('scripts')
</body>
</html>
