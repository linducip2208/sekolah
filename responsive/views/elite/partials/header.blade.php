@php $p = $platform ?? app(\App\Services\PlatformSettingsService::class)->all(); @endphp
<header x-data="{ mobileNav: false }"
        class="paper border-b border-rule sticky top-0 z-30 backdrop-blur"
        style="background: rgba(248,245,238,.92);">
    <div class="max-w-7xl mx-auto px-3 sm:px-5 lg:px-6">
        <div class="hidden md:flex items-center justify-between py-2.5 border-b border-rule/60">
            <div class="flex items-center gap-5 text-xs" style="color: var(--c-muted); font-family: 'Inter', sans-serif; letter-spacing: .12em;">
                <span class="uppercase">Est. {{ $p['established_year'] ?? '1890' }}</span>
                <span class="opacity-50">|</span>
                <span class="font-script italic" style="font-size: 1rem;">{{ $p['motto_latin'] ?? 'Floreat Schola' }}</span>
            </div>
            <div class="flex items-center gap-4 text-xs" style="color: var(--c-muted);">
                @if(!empty($p['contact_phone']))<a href="tel:{{ $p['contact_phone'] }}" class="hover:text-[var(--c-primary)]">{{ $p['contact_phone'] }}</a>@endif
                @if(!empty($p['contact_email']))<span class="opacity-50">|</span><a href="mailto:{{ $p['contact_email'] }}" class="hover:text-[var(--c-primary)]">{{ $p['contact_email'] }}</a>@endif
            </div>
        </div>

        <div class="flex items-center justify-between gap-3 py-3 sm:py-5">
            <a href="/" class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                @if(!empty($p['logo_url']))
                    <img src="{{ $p['logo_url'] }}" alt="{{ $p['app_name'] }}" class="h-10 sm:h-12 w-auto flex-shrink-0">
                @else
                    <div class="crest-mark flex-shrink-0">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 2 7l10 5 10-5-10-5zm0 7L2 14l10 5 10-5-10-5z"/></svg>
                    </div>
                @endif
                <div class="leading-tight min-w-0">
                    <div class="elite-h3 text-base sm:text-xl ink-primary truncate">{{ $p['app_name'] ?? 'eSchool Academy' }}</div>
                    <div class="elite-kicker text-[.55rem] sm:text-[.6rem] mt-0.5 truncate" style="letter-spacing:.3em;">{{ $p['institution_type'] ?? 'Academy' }}</div>
                </div>
            </a>

            <nav class="hidden lg:flex items-center gap-8 text-sm uppercase tracking-widest" style="font-family:'Inter',sans-serif; font-weight:500; color: var(--c-primary); font-size: .72rem; letter-spacing:.18em;">
                <a href="/" class="hover:ink-accent transition">Beranda</a>
                <a href="/#fitur" class="hover:ink-accent transition">Akademi</a>
                <a href="/#modul" class="hover:ink-accent transition">Program</a>
                <a href="/docs" class="hover:ink-accent transition">Panduan</a>
                <a href="/#harga" class="hover:ink-accent transition">Penerimaan</a>
            </nav>

            <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                <a href="{{ route('admin.login') }}" class="hidden sm:inline-block btn-elite-ghost" style="padding:.55rem 1rem;font-size:.65rem;">Login</a>
                <a href="{{ route('admin.login') }}" class="hidden xs:inline-block btn-elite" style="padding:.6rem 1.1rem;font-size:.65rem;">
                    <span class="hidden sm:inline">Daftar Sekolah</span>
                    <span class="sm:hidden">Daftar</span>
                </a>
                {{-- Mobile menu toggle --}}
                <button type="button" @click="mobileNav = !mobileNav"
                        class="lg:hidden p-2 ink-primary hover:ink-accent transition"
                        :aria-expanded="mobileNav.toString()"
                        aria-label="Menu">
                    <svg x-show="!mobileNav" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileNav" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile collapsible nav --}}
        <nav x-show="mobileNav" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="lg:hidden border-t border-rule/60 py-2 -mx-3 sm:-mx-5 px-3 sm:px-5 flex flex-col uppercase"
             style="font-family:'Inter',sans-serif; font-weight:500; color: var(--c-primary); font-size: .72rem; letter-spacing:.18em;">
            <a href="/" class="py-2.5 hover:ink-accent transition border-b border-rule/40">Beranda</a>
            <a href="/#fitur" class="py-2.5 hover:ink-accent transition border-b border-rule/40">Akademi</a>
            <a href="/#modul" class="py-2.5 hover:ink-accent transition border-b border-rule/40">Program</a>
            <a href="/docs" class="py-2.5 hover:ink-accent transition border-b border-rule/40">Panduan</a>
            <a href="/#harga" class="py-2.5 hover:ink-accent transition border-b border-rule/40">Penerimaan</a>
            <a href="{{ route('admin.login') }}" class="py-2.5 hover:ink-accent transition sm:hidden">Login</a>
        </nav>
    </div>
</header>
