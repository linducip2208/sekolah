<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"
      x-data="{ sidebarOpen: window.innerWidth >= 1024, isMobile: window.innerWidth < 1024 }"
      x-init="window.addEventListener('resize', () => { isMobile = window.innerWidth < 1024; if (!isMobile) sidebarOpen = true; else sidebarOpen = false; })">
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
        $displayName = $branding['display_name'] ?? config('app.name', 'eSchool');
        $logoPrimary = $branding['logos']['primary'] ?? null;
        $favicon = $branding['logos']['favicon'] ?? null;
        $cacheVer = $branding['cache_version'] ?? 1;
    @endphp
    <title>@yield('title', 'Dashboard') — {{ $displayName }}</title>

    @if($favicon)
        <link rel="icon" href="{{ $favicon }}?v={{ $cacheVer }}">
    @endif

    @if(auth()->check() && auth()->user()->school_id)
        @php $gfu = $branding['font']['google_fonts_url'] ?? null; @endphp
        @if($gfu)<link rel="stylesheet" href="{{ $gfu }}">@endif
        <link rel="stylesheet" href="{{ route('branding.css', ['schoolId' => auth()->user()->school_id, 'v' => $cacheVer]) }}">
    @endif

    @include('elite.partials.head')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            primary:   '{{ $brandPrimary }}',
                            secondary: '{{ $brandSecondary }}',
                            success:   '{{ $brandSuccess }}',
                            warning:   '{{ $brandWarning }}',
                            danger:    '{{ $brandDanger }}',
                        },
                    },
                },
            },
        };
    </script>
    <style>
        :root {
            --brand-primary:   {{ $brandPrimary }};
            --brand-secondary: {{ $brandSecondary }};
            --brand-success:   {{ $brandSuccess }};
            --brand-warning:   {{ $brandWarning }};
            --brand-danger:    {{ $brandDanger }};
        }
        .btn-brand {
            display:inline-flex;align-items:center;justify-content:center;
            background-color: var(--c-primary);
            color: white;
            padding: .7rem 1.4rem;
            border: 1px solid var(--c-primary);
            font-family: 'Inter', sans-serif;
            font-size: .72rem;
            letter-spacing: .18em;
            text-transform: uppercase;
            font-weight: 600;
            transition: all .25s ease;
        }
        .btn-brand:hover { background: var(--c-secondary); border-color: var(--c-secondary); }
        .sidebar-link {
            display: flex; align-items: center; gap: .85rem;
            padding: .65rem 1rem;
            font-family: 'Inter', sans-serif;
            font-size: .7rem; letter-spacing: .15em; text-transform: uppercase; font-weight: 500;
            color: rgba(255,255,255,.78);
            transition: all .25s ease;
            border-left: 2px solid transparent;
        }
        .sidebar-link:hover { color: var(--c-accent); background: rgba(255,255,255,.05); }
        .sidebar-link.active { color: var(--c-accent); background: rgba(255,255,255,.07); border-left-color: var(--c-accent); }
    </style>
    @stack('head')
</head>
<body class="paper antialiased">

{{-- Offline Status Indicator --}}
@include('offline-status')

{{-- Offline DB & Sync Scripts --}}
<script src="{{ asset('js/offline-db.js') }}"></script>
<script src="{{ asset('js/offline-sync.js') }}"></script>

<div class="flex h-screen overflow-hidden">
    {{-- Mobile backdrop --}}
    <div x-show="sidebarOpen && isMobile" x-cloak
         @click="sidebarOpen = false"
         class="sidebar-backdrop lg:hidden"></div>

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
                @if($logoPrimary)
                    <img src="{{ $logoPrimary }}?v={{ $cacheVer }}" alt="{{ $displayName }}" class="h-11 w-11 bg-white p-1 flex-shrink-0">
                @else
                    <div class="crest-mark flex-shrink-0" style="border-color: var(--c-accent); color: var(--c-accent);">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 2 7l10 5 10-5-10-5zm0 7L2 14l10 5 10-5-10-5z"/></svg>
                    </div>
                @endif
                <div class="min-w-0 leading-tight">
                    <div class="elite-h3 text-base text-white truncate">{{ $displayName }}</div>
                    @if(!empty($branding['tagline']))
                        <div class="font-script italic text-xs mt-0.5 truncate" style="color: var(--c-accent);">{{ $branding['tagline'] }}</div>
                    @else
                        <div class="elite-kicker mt-1 text-[.55rem]" style="color: var(--c-accent);">Manus Magistri</div>
                    @endif
                </div>
            </div>
            <button @click="sidebarOpen = false" type="button"
                    class="lg:hidden text-white/70 hover:text-white p-1 -mr-1"
                    aria-label="Tutup menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <nav class="flex-1 px-2 py-4 space-y-0.5 overflow-y-auto">
            @yield('sidebar')
        </nav>

        <div class="px-4 py-5 border-t" style="border-color: rgba(255,255,255,.12);">
            <div class="font-script italic text-base mb-3" style="color: var(--c-accent);">"{{ $platform['motto_latin'] ?? 'Floreat Schola' }}"</div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="elite-kicker text-[.65rem] hover:text-white transition" style="color: rgba(255,255,255,.5);">↩ Keluar Sesi</button>
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
                    <div class="elite-kicker truncate" style="color: var(--c-muted);">@yield('title', 'Administrator')</div>
                </div>
                <div class="flex items-center gap-2 sm:gap-4 flex-shrink-0">
                    {{-- Mobile search icon --}}
                    <button onclick="window.dispatchEvent(new CustomEvent('open-search'))"
                            class="md:hidden text-gray-500 hover:ink-primary p-1"
                            aria-label="Cari">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>

                    {{-- Desktop search trigger --}}
                    <button onclick="window.dispatchEvent(new CustomEvent('open-search'))" class="hidden md:flex items-center gap-2 px-3 py-1.5 border border-rule text-xs text-gray-500 hover:border-[var(--c-accent)]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>{{ __('common.search') }}…</span>
                        <span class="font-mono text-[.6rem] px-1 border border-rule">⌘K</span>
                    </button>

                    {{-- Locale switcher --}}
                    <div class="hidden sm:flex gap-1 text-xs">
                        <a href="?lang=id" class="px-2 py-1 {{ app()->getLocale() === 'id' ? 'bg-[var(--c-accent)] text-white' : 'border border-rule' }}">ID</a>
                        <a href="?lang=en" class="px-2 py-1 {{ app()->getLocale() === 'en' ? 'bg-[var(--c-accent)] text-white' : 'border border-rule' }}">EN</a>
                    </div>

                    <a href="{{ route('profile.edit') }}" class="text-right hidden md:block hover:opacity-80">
                        <div class="font-serif text-base ink-primary leading-tight truncate max-w-[140px]">{{ auth()->user()?->name }}</div>
                        <div class="elite-kicker text-[.55rem]" style="color: var(--c-muted);">{{ __('nav.profile') }}</div>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="crest-mark" style="width:2.5rem;height:2.5rem; border-color: var(--c-accent); color: var(--c-primary); background: rgba(184,134,11,.08);" aria-label="{{ __('nav.profile') }}">
                        <span class="font-display text-sm">{{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}</span>
                    </a>
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
            @if($errors->any())
                <div class="mb-5 px-5 py-3 bg-white border-l-4 border-red-700">
                    <ul class="list-disc list-inside font-serif text-base text-red-800">
                        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

{{-- ===== Global Search Modal (Cmd+K) ===== --}}
<div x-data="globalSearch()"
     x-init="$nextTick(() => { window.addEventListener('open-search', () => { open = true; $nextTick(() => $refs.input.focus()) }); document.addEventListener('keydown', (e) => { if ((e.metaKey || e.ctrlKey) && e.key === 'k') { e.preventDefault(); open = true; $nextTick(() => $refs.input.focus()) } if (e.key === 'Escape') open = false; }); })"
     x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-start justify-center pt-10 sm:pt-20 px-3 sm:px-4"
     style="background: rgba(11,29,58,.75);">
    <div @click.outside="open = false" class="bg-white w-full max-w-2xl shadow-2xl border border-rule">
        <div class="flex items-center px-5 py-4 border-b border-rule">
            <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input x-ref="input" x-model="query" @input.debounce.300ms="search()"
                   placeholder="Cari siswa, staff, invoice, pengumuman..."
                   class="flex-1 outline-none font-serif text-lg">
            <span class="font-mono text-xs text-gray-400 px-2 py-1 border border-rule">ESC</span>
        </div>
        <div class="max-h-96 overflow-y-auto">
            <template x-if="loading">
                <div class="p-6 text-center text-gray-500 italic font-serif">Mencari...</div>
            </template>
            <template x-if="!loading && results.length === 0 && query.length >= 2">
                <div class="p-6 text-center text-gray-500 italic font-serif">Tidak ada hasil untuk "<span x-text="query"></span>"</div>
            </template>
            <template x-if="!loading && query.length < 2">
                <div class="p-6 text-center text-gray-400 text-sm font-serif">Ketik minimal 2 karakter...</div>
            </template>
            <template x-for="r in results" :key="r.url">
                <a :href="r.url" class="block px-5 py-3 hover:bg-gray-50 border-b border-rule last:border-0">
                    <div class="flex items-center gap-3">
                        <span class="text-xl" x-text="r.icon"></span>
                        <div class="flex-1 min-w-0">
                            <div class="font-serif font-semibold ink-primary truncate" x-text="r.title"></div>
                            <div class="text-xs text-gray-500 truncate" x-text="r.sub"></div>
                        </div>
                        <span class="elite-kicker text-[.55rem] text-gray-400" x-text="r.type"></span>
                    </div>
                </a>
            </template>
        </div>
    </div>
</div>
<script>
function globalSearch() {
    return {
        open: false, query: '', loading: false, results: [],
        async search() {
            if (this.query.length < 2) { this.results = []; return; }
            this.loading = true;
            try {
                const res = await fetch('{{ route("admin.search") }}?q=' + encodeURIComponent(this.query));
                const data = await res.json();
                this.results = data.results || [];
            } finally { this.loading = false; }
        }
    };
}
</script>

<script>
// Auto-close mobile sidebar drawer when a nav link is tapped.
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

@stack('scripts')

{{-- Floating Panic Button --}}
<div x-data="{ open: false }" class="fixed bottom-6 right-6 z-50">
    <button @click="open = !open"
            class="w-14 h-14 rounded-full bg-red-600 text-white shadow-lg hover:bg-red-700 transition flex items-center justify-center animate-pulse"
            style="box-shadow: 0 0 20px rgba(220,38,38,.5);"
            title="Panic Button">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
    </button>
    <div x-show="open" x-cloak @click.outside="open = false"
         class="absolute bottom-16 right-0 w-80 bg-white border border-rule shadow-2xl p-5">
        <div class="flex justify-between items-center mb-3">
            <h3 class="font-serif font-semibold text-red-700 text-base">Peringatan Darurat</h3>
            <button @click="open = false" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.emergency.quick') }}">
            @csrf
            <div class="space-y-3">
                <div>
                    <select name="alert_type" class="w-full border border-rule p-2 text-sm">
                        <option value="security">🛡️ Keamanan</option>
                        <option value="fire">🔥 Kebakaran</option>
                        <option value="earthquake">🌍 Gempa</option>
                        <option value="flood">🌊 Banjir</option>
                        <option value="medical">🏥 Medis</option>
                        <option value="other">⚠️ Lainnya</option>
                    </select>
                </div>
                <div>
                    <textarea name="message" rows="3" required class="w-full border border-rule p-2 text-sm"
                              placeholder="Deskripsikan situasi darurat..."></textarea>
                </div>
                <button type="submit" class="w-full py-2.5 text-white text-sm font-semibold bg-red-600 hover:bg-red-700 transition"
                        onclick="return confirm('Kirim peringatan darurat ke SEMUA?')">
                    KIRIM PENTING!
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
