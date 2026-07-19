<!DOCTYPE html>
<html lang="id"
      x-data="{ sidebarOpen: window.innerWidth >= 1024, isMobile: window.innerWidth < 1024 }"
      x-init="window.addEventListener('resize', () => { isMobile = window.innerWidth < 1024; if (!isMobile) sidebarOpen = true; else sidebarOpen = false; })">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Operator Platform') — {{ $platform['app_name'] ?? 'eSchool Academy' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('elite.partials.head')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar-link {
            display: flex; align-items: center; gap: .85rem;
            padding: .7rem 1rem;
            font-family: 'Inter', sans-serif;
            font-size: .72rem; letter-spacing: .15em; text-transform: uppercase; font-weight: 500;
            color: rgba(255,255,255,.78);
            transition: all .25s ease;
            border-left: 2px solid transparent;
        }
        .sidebar-link:hover { color: var(--c-accent); background: rgba(255,255,255,.04); }
        .sidebar-link.active { color: var(--c-accent); background: rgba(255,255,255,.06); border-left-color: var(--c-accent); }
    </style>
</head>
<body class="paper antialiased">

<div class="flex h-screen overflow-hidden">
    {{-- Mobile backdrop --}}
    <div x-show="sidebarOpen && isMobile" x-cloak
         @click="sidebarOpen = false"
         class="sidebar-backdrop lg:hidden"></div>

    {{-- Sidebar — dark navy w/ gold accents --}}
    <aside x-show="sidebarOpen" x-cloak
           x-transition:enter="transition transform ease-out duration-200"
           x-transition:enter-start="-translate-x-full lg:translate-x-0"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition transform ease-in duration-150"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full lg:translate-x-0"
           class="w-72 sidebar-mobile-drawer lg:relative lg:flex flex flex-col flex-shrink-0 text-white"
           style="background: var(--c-primary);">
        <div class="px-5 sm:px-6 py-5 sm:py-7 border-b flex items-start justify-between gap-2" style="border-color: rgba(255,255,255,.12);">
            <div class="flex items-center gap-3 flex-1 min-w-0">
                @if(!empty($platform['logo_dark_url']))
                    <img src="{{ $platform['logo_dark_url'] }}" alt="" class="h-10 w-auto flex-shrink-0">
                @elseif(!empty($platform['logo_url']))
                    <img src="{{ $platform['logo_url'] }}" alt="" class="h-10 w-auto brightness-0 invert flex-shrink-0">
                @else
                    <div class="crest-mark flex-shrink-0" style="border-color: var(--c-accent); color: var(--c-accent);">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 2 7l10 5 10-5-10-5zm0 7L2 14l10 5 10-5-10-5z"/></svg>
                    </div>
                @endif
                <div class="min-w-0">
                    <div class="elite-h3 text-lg text-white leading-none truncate">{{ $platform['app_name'] ?? 'eSchool' }}</div>
                    <div class="elite-kicker mt-1.5 text-[.55rem]" style="color: var(--c-accent); letter-spacing: .25em;">Magisterium</div>
                </div>
            </div>
            <button @click="sidebarOpen = false" type="button"
                    class="lg:hidden text-white/70 hover:text-white p-1 -mr-1"
                    aria-label="Tutup menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <nav class="flex-1 px-2 py-4 space-y-0.5 overflow-y-auto">
            <a href="{{ route('super.dashboard') }}" class="sidebar-link {{ request()->routeIs('super.dashboard') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="{{ route('super.schools.index') }}" class="sidebar-link {{ request()->routeIs('super.schools.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Sekolah
            </a>
            <a href="{{ route('super.plans.index') }}" class="sidebar-link {{ request()->routeIs('super.plans.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Tingkatan
            </a>
            <a href="{{ route('super.subscriptions.index') }}" class="sidebar-link {{ request()->routeIs('super.subscriptions.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Langganan
            </a>
            @php
                try {
                    $pendingRegistrations = \App\Models\Platform\SchoolRegistration::whereIn('status', ['pending','verifying','paid'])->count();
                } catch (\Throwable) { $pendingRegistrations = 0; }
            @endphp
            <a href="{{ route('super.registrations.index') }}" class="sidebar-link {{ request()->routeIs('super.registrations.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Pendaftaran</span>
                @if($pendingRegistrations > 0)
                    <span class="ml-auto text-[.6rem] font-bold px-1.5 py-0.5 rounded" style="background:var(--c-accent); color:var(--c-primary);">{{ $pendingRegistrations }}</span>
                @endif
            </a>
            <div class="elite-kicker mx-4 mt-3 mb-1.5 text-[.5rem]" style="color: rgba(184,134,11,.6); letter-spacing:.25em;">Penerimaan Sekolah ⇢ Anda</div>
            <a href="{{ route('super.billing.accounts.index') }}" class="sidebar-link {{ request()->routeIs('super.billing.accounts.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Rekening Manual
            </a>
            <a href="{{ route('super.billing.gateways.index') }}" class="sidebar-link {{ request()->routeIs('super.billing.gateways.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Gateway Online
            </a>
            <a href="{{ route('super.analytics') }}" class="sidebar-link {{ request()->routeIs('super.analytics') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Analitik
            </a>
            <a href="{{ route('super.benchmark.index') }}" class="sidebar-link {{ request()->routeIs('super.benchmark.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                Benchmark
            </a>

            <div class="elite-kicker my-4 mx-4 text-[.55rem]" style="color: rgba(255,255,255,.4); letter-spacing:.3em;">— Tata Kelola —</div>
            <a href="{{ route('super.users.index') }}" class="sidebar-link {{ request()->routeIs('super.users.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Pengguna
            </a>
            <a href="{{ route('super.foundations.index') }}" class="sidebar-link {{ request()->routeIs('super.foundations.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Yayasan
            </a>
            <a href="{{ route('super.announcements.index') }}" class="sidebar-link {{ request()->routeIs('super.announcements.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                Pengumuman
            </a>
            <a href="{{ route('super.audit.index') }}" class="sidebar-link {{ request()->routeIs('super.audit.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Audit Log
            </a>

            <div class="elite-kicker my-4 mx-4 text-[.55rem]" style="color: rgba(255,255,255,.4); letter-spacing:.3em;">— Sistem —</div>
            <a href="{{ route('super.system.health') }}" class="sidebar-link {{ request()->routeIs('super.system.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                Health Check
            </a>
            <a href="{{ route('super.reports.index') }}" class="sidebar-link {{ request()->routeIs('super.reports.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Laporan
            </a>
            <a href="{{ route('super.email-templates.index') }}" class="sidebar-link {{ request()->routeIs('super.email-templates.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Email Templates
            </a>
            <a href="{{ route('super.backups.index') }}" class="sidebar-link {{ request()->routeIs('super.backups.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                Backup
            </a>
            <a href="{{ route('super.maintenance.index') }}" class="sidebar-link {{ request()->routeIs('super.maintenance.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                Maintenance
            </a>
            <a href="{{ route('super.webhooks.index') }}" class="sidebar-link {{ request()->routeIs('super.webhooks.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Webhook Logs
            </a>
            <a href="{{ route('pair.show') }}" class="sidebar-link {{ request()->routeIs('pair.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Lisensi
            </a>

            <div class="elite-kicker my-4 mx-4 text-[.55rem]" style="color: rgba(255,255,255,.4); letter-spacing:.3em;">— Kustomisasi —</div>
            <a href="{{ route('super.whitelabel.show') }}" class="sidebar-link {{ request()->routeIs('super.whitelabel.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                Whitelabel
            </a>
            <a href="{{ route('super.config') }}" class="sidebar-link {{ request()->routeIs('super.config') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Konfigurasi
            </a>
        </nav>

        <div class="px-4 py-5 border-t" style="border-color: rgba(255,255,255,.12);">
            <div class="font-script italic text-base mb-3" style="color: var(--c-accent);">"{{ $platform['motto_latin'] ?? 'Floreat Schola' }}"</div>
            <form method="POST" action="{{ route('super.logout') }}">
                @csrf
                <button type="submit" class="elite-kicker text-[.65rem] hover:text-white transition" style="color: rgba(255,255,255,.5);">
                    ↩ Keluar Sesi
                </button>
            </form>
        </div>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden min-w-0">
        <header class="bg-white border-b border-rule">
            <div class="flex items-center justify-between gap-2 px-3 sm:px-5 lg:px-7 py-3 sm:py-4">
                <div class="flex items-center gap-2 sm:gap-4 min-w-0">
                    <button @click="sidebarOpen = !sidebarOpen" type="button"
                            class="text-gray-500 hover:ink-primary p-1 -ml-1"
                            aria-label="Toggle menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="elite-kicker truncate" style="color: var(--c-muted);">@yield('title', 'Operator Platform')</div>
                </div>
                <div class="flex items-center gap-2 sm:gap-4 flex-shrink-0">
                    <div class="text-right hidden md:block">
                        <div class="font-serif text-base ink-primary leading-tight truncate max-w-[160px]">{{ auth()->user()?->name ?? 'Operator' }}</div>
                        <div class="elite-kicker text-[.55rem]" style="color: var(--c-muted);">Magister</div>
                    </div>
                    <div class="crest-mark" style="width:2.5rem;height:2.5rem; border-color: var(--c-accent); color: var(--c-primary); background: rgba(184,134,11,.08);">
                        <span class="font-display text-sm">{{ strtoupper(substr(auth()->user()?->name ?? 'M', 0, 1)) }}</span>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto overflow-x-hidden p-3 sm:p-5 lg:p-7" style="background: var(--c-paper);">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     class="mb-5 px-5 py-3 bg-white border-l-4 deco-frame flex justify-between items-center" style="border-color: var(--c-accent);">
                    <span class="font-serif text-base ink-primary"><span class="ink-accent mr-2">❦</span>{{ session('success') }}</span>
                    <button @click="show = false" class="ink-accent">✕</button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
document.addEventListener('click', (e) => {
    if (window.innerWidth >= 1024) return;
    const link = e.target.closest('aside a[href]');
    if (!link) return;
    const root = document.documentElement;
    if (root._x_dataStack && root._x_dataStack[0]) {
        root._x_dataStack[0].sidebarOpen = false;
    }
});
</script>

</body>
</html>
