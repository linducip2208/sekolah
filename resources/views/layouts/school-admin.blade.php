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
        $brandPrimary = $branding['colors']['primary'] ?? '#2563EB';
        $brandSecondary = $branding['colors']['secondary'] ?? '#64748B';
        $brandSuccess = $branding['colors']['success'] ?? '#16A34A';
        $brandWarning = $branding['colors']['warning'] ?? '#EAB308';
        $brandDanger = $branding['colors']['danger'] ?? '#DC2626';
        $displayName = $branding['display_name'] ?? config('app.name', 'Sikad Pro');
        $logoPrimary = $branding['logos']['primary'] ?? null;
        $favicon = $branding['logos']['favicon'] ?? null;
        $cacheVer = $branding['cache_version'] ?? 1;
    @endphp
    <title>@yield('title', 'Dashboard') — {{ $displayName }}</title>

    @if($favicon)
        <link rel="icon" href="{{ $favicon }}?v={{ $cacheVer }}">
    @endif

    {{-- Base assets: CDN Tailwind + fonts + legacy elite classes + platform brand vars --}}
    @include('elite.partials.head')

    {{-- Per-school white-label theme (overrides platform tokens + loads theme font) --}}
    @if(auth()->check() && auth()->user()->school_id)
        @php $gfu = $branding['font']['google_fonts_url'] ?? null; @endphp
        @if($gfu)<link rel="stylesheet" href="{{ $gfu }}">@endif
        <link rel="stylesheet" href="{{ route('branding.css', ['schoolId' => auth()->user()->school_id, 'v' => $cacheVer]) }}">
    @endif

    {{-- Alpine plugins + core --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Design system (pure CSS, Vite-built) --}}
    @vite(['resources/css/app.css'])

    {{-- Apply persisted theme before paint (avoid FOUC) --}}
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
<body x-data="sidebarState()" x-init="init()" class="antialiased">

<x-layouts.skip-link />

{{-- Offline Status Indicator --}}
@include('offline-status')

{{-- Offline DB & Sync Scripts --}}
<script src="{{ asset('js/offline-db.js') }}"></script>
<script src="{{ asset('js/offline-sync.js') }}"></script>

{{-- Global Alpine components (loaded as classic script BEFORE Alpine defer) --}}
<script src="{{ Vite::asset('resources/js/app.js') }}"></script>

<div class="flex h-screen overflow-hidden">

    {{-- Mobile backdrop --}}
    <div x-show="open && mobile" x-cloak @click="closeMobile()" class="sidebar-backdrop lg:hidden"></div>

    {{-- ===== SIDEBAR ===== --}}
    <aside x-show="open || !mobile" x-cloak
           :class="collapsed && !mobile ? 'app-sidebar collapsed' : 'app-sidebar'"
           x-transition:enter="transition-transform ease-out duration-200"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition-transform ease-in duration-150"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full"
           class="fixed inset-y-0 left-0 z-40 w-72 lg:static lg:translate-x-0 flex flex-col flex-shrink-0 text-white"
           style="background: var(--c-primary);">
        {{-- Brand --}}
        <div class="px-5 sm:px-6 py-5 border-b flex items-start justify-between gap-2" style="border-color: rgba(255,255,255,.12);">
            <div class="flex items-center gap-3 flex-1 min-w-0 sidebar-brand">
                @if($logoPrimary)
                    <img src="{{ $logoPrimary }}?v={{ $cacheVer }}" alt="{{ $displayName }}" class="h-10 w-10 rounded-lg bg-white p-1 flex-shrink-0">
                @else
                    <div class="crest-mark flex-shrink-0" style="border-color: var(--c-accent); color: var(--c-accent); width:2.5rem;height:2.5rem;">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 2 7l10 5 10-5-10-5zm0 7L2 14l10 5 10-5-10-5z"/></svg>
                    </div>
                @endif
                <div class="min-w-0 leading-tight sidebar-brand-text">
                    <div class="text-base font-bold text-white truncate">{{ $displayName }}</div>
                    <div class="text-[11px] font-medium mt-0.5 truncate" style="color: var(--c-accent);">Panel Sekolah</div>
                </div>
            </div>
            <button @click="closeMobile()" type="button" class="lg:hidden text-white/70 hover:text-white p-1 -mr-1" aria-label="Tutup menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-2 py-3 space-y-0.5 overflow-y-auto" @click="closeMobile()">
            @yield('sidebar')
        </nav>

        {{-- Footer --}}
        <div class="px-4 py-4 border-t sidebar-footer-text" style="border-color: rgba(255,255,255,.12);">
            <div class="text-[11px] text-white/60 truncate mb-2">{{ auth()->user()?->email }}</div>
            <div class="flex items-center gap-2">
                <button type="button" @click="toggleCollapse()" class="hidden lg:inline-flex items-center gap-2 text-[12px] text-white/60 hover:text-white transition" :title="collapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                    <span x-text="collapsed ? '' : 'Ciutkan'"></span>
                </button>
                <form method="POST" action="{{ route('admin.logout') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="text-[12px] text-white/60 hover:text-white transition">↩ Keluar Sesi</button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden min-w-0">

        {{-- ===== HEADER / TOPBAR ===== --}}
        <header class="topbar sticky top-0 z-30">
            <div class="flex items-center justify-between gap-2 px-3 sm:px-5 lg:px-6 py-2.5">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    <button @click="mobile ? toggleMobile() : toggleCollapse()" type="button" class="btn-icon" aria-label="Buka/tutup menu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="min-w-0">
                        @if(!empty($breadcrumbs))
                            <x-navigation.breadcrumbs :items="$breadcrumbs" />
                        @endif
                        <div class="text-sm font-semibold text-[var(--color-text)] truncate">@yield('title', 'Administrator')</div>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 sm:gap-2 flex-shrink-0">
                    {{-- Search trigger --}}
                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-search'))" class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-lg border border-[var(--color-border)] text-sm text-[var(--color-text-muted)] hover:border-[var(--color-primary)]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>{{ __('common.search') ?? 'Cari' }}…</span>
                        <span class="command-kbd">⌘K</span>
                    </button>
                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-search'))" class="btn-icon md:hidden" aria-label="Cari">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>

                    <x-navigation.notification-center />
                    <x-navigation.theme-toggle />
                    <x-navigation.help-center />

                    <div class="hidden sm:block mx-1 w-px h-6 bg-[var(--color-border)]"></div>

                    <x-navigation.profile-menu :name="auth()->user()?->name" :role="auth()->user()?->role" :profileRoute="route('profile.edit')" :notificationsRoute="route('admin.notifications.index')" logoutRoute="admin.logout" />
                </div>
            </div>
        </header>

        {{-- ===== MAIN ===== --}}
        <main id="main-content" class="flex-1 overflow-y-auto overflow-x-hidden p-3 sm:p-5 lg:p-6" style="background: var(--color-background);" tabindex="-1">
            {{-- Server flash → inline alerts (reliable on first paint) --}}
            @if(session('success'))
                <x-ui.alert variant="success" dismissible class="mb-4">{{ session('success') }}</x-ui.alert>
            @endif
            @if(session('error'))
                <x-ui.alert variant="danger" dismissible class="mb-4">{{ session('error') }}</x-ui.alert>
            @endif
            @if(isset($errors) && $errors->any())
                <x-ui.alert variant="danger" class="mb-4">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </x-ui.alert>
            @endif

            @yield('content')
        </main>
    </div>
</div>

{{-- ===== COMMAND PALETTE ===== --}}
<x-navigation.command-palette />

{{-- ===== TOAST ===== --}}
<x-overlays.toast />

{{-- ===== CONFIRM DIALOG ===== --}}
<x-overlays.confirm-dialog />

@stack('scripts')

{{-- Sidebar collapsed tooltips (desktop) --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var setTitles = function () {
            document.querySelectorAll('.app-sidebar').forEach(function (aside) {
                var collapsed = aside.classList.contains('collapsed');
                aside.querySelectorAll('.sidebar-link, .sidebar-sub-link').forEach(function (link) {
                    if (collapsed) {
                        if (!link.dataset.t) link.dataset.t = link.textContent.trim();
                        link.setAttribute('title', link.dataset.t);
                    } else {
                        link.removeAttribute('title');
                    }
                });
            });
        };
        var mo = new MutationObserver(setTitles);
        document.querySelectorAll('.app-sidebar').forEach(function (a) {
            mo.observe(a, { attributes: true, attributeFilter: ['class'] });
        });
        setTitles();
    });
</script>

{{-- ===== EMERGENCY (discreet) ===== --}}
<div x-data="{ open: false, typed: '', mode: 'security' }" class="fixed bottom-5 right-5 z-40">
    <button @click="open = !open" type="button"
            class="flex items-center gap-2 px-3 py-2 rounded-lg border border-[var(--color-danger)] text-[var(--color-danger)] bg-[var(--color-surface)] hover:bg-[var(--color-danger-soft)] text-sm font-semibold shadow-sm"
            title="Peringatan darurat">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span class="hidden sm:inline">Darurat</span>
    </button>

    <div x-show="open" x-cloak @click.outside="open = false" x-trap.inert.noscroll="open" class="fixed inset-0 z-40 flex items-end sm:items-center justify-center sm:p-4" style="background: rgba(11,29,58,.55);">
        <div class="w-full max-w-md bg-[var(--color-surface)] border border-[var(--color-border)] rounded-t-2xl sm:rounded-xl shadow-2xl" style="max-height: 90vh; overflow-y: auto;">
            <div class="flex items-center justify-between px-5 py-4 border-b border-[var(--color-border)]">
                <h3 class="font-bold text-[var(--color-danger)]">Peringatan Darurat</h3>
                <button @click="open = false" type="button" class="btn-icon" aria-label="Tutup"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form method="POST" action="{{ route('admin.emergency.quick') }}" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="label" for="em-type">Kategori</label>
                    <select name="alert_type" id="em-type" x-model="mode" class="select">
                        <option value="security">🛡️ Keamanan</option>
                        <option value="fire">🔥 Kebakaran</option>
                        <option value="earthquake">🌍 Gempa</option>
                        <option value="flood">🌊 Banjir</option>
                        <option value="medical">🏥 Medis</option>
                        <option value="other">⚠️ Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="label" for="em-msg">Pesan siaran <span class="req">*</span></label>
                    <textarea name="message" id="em-msg" rows="3" required class="textarea" placeholder="Deskripsikan situasi darurat…"></textarea>
                </div>
                <div class="text-xs text-[var(--color-text-muted)]">
                    Siaran ini akan dikirim ke <strong>seluruh pengguna</strong> sekolah melalui channel aktif (WA, push, email).
                </div>
                <div>
                    <label class="label" for="em-confirm">Ketik <code>DARURAT</code> untuk konfirmasi</label>
                    <input type="text" id="em-confirm" x-model="typed" class="input" autocomplete="off">
                </div>
                <button type="submit" class="btn btn-danger w-full" :disabled="typed.toUpperCase() !== 'DARURAT'" x-bind:class="typed.toUpperCase() !== 'DARURAT' && 'opacity-50 cursor-not-allowed'">
                    Siarkan Peringatan
                </button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
