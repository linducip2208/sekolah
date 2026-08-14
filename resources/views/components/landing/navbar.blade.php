@props(['theme' => []])
@php
    $nav = [
        ['label' => 'Fitur', 'href' => '#fitur'],
        ['label' => 'Solusi', 'href' => '#solusi'],
        ['label' => 'Harga', 'href' => route('public.pricing')],
        ['label' => 'Panduan', 'href' => '/docs'],
        ['label' => 'Blog', 'href' => route('blog.index')],
    ];
    $logo = $platform['logo_url'] ?? null;
    $demoHref = $platform['whatsapp_link'] ?? route('public.pricing');
@endphp

<header x-data="{ open: false }" class="lp-nav">
    <div class="lp-container flex items-center justify-between h-16 lg:h-[72px]">
        <a href="/" class="flex items-center gap-2.5 shrink-0" aria-label="{{ $platform['app_name'] }} — beranda">
            @if($logo)
                <img src="{{ $logo }}" alt="" class="h-9 w-auto">
            @else
                <span class="h-9 w-9 rounded-lg flex items-center justify-center font-bold text-white" style="background: var(--lp-primary);">{{ Str::upper(Str::substr($platform['app_name'] ?? 'S', 0, 1)) }}</span>
            @endif
            <span class="font-display font-bold text-lg leading-tight" style="color: var(--lp-ink);">{{ $platform['app_name'] ?? 'Sikad Pro' }}</span>
        </a>

        <nav class="hidden lg:flex items-center gap-8" aria-label="Navigasi utama">
            @foreach($nav as $item)
                <a href="{{ $item['href'] }}" class="lp-nav-link">{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="hidden lg:flex items-center gap-3">
            <a href="{{ route('admin.login') }}" class="lp-btn-ghost lp-btn" style="min-height: 40px; padding: .5rem 1.1rem;">Login</a>
            <a href="{{ $demoHref }}" class="lp-btn" style="min-height: 40px; padding: .5rem 1.25rem;">Request Demo</a>
        </div>

        <button type="button" class="lg:hidden inline-flex items-center justify-center w-11 h-11 rounded-lg" style="color: var(--lp-ink);" @click="open = true" :aria-expanded="open.toString()" aria-controls="mobile-menu" aria-label="Buka menu">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    {{-- Mobile drawer --}}
    <div id="mobile-menu" x-show="open" x-cloak x-trap.inert.noscroll="open"
         class="fixed inset-0 z-50 lg:hidden">
        <div class="absolute inset-0" style="background: rgba(15,23,42,.55);" @click="open = false" aria-hidden="true"></div>
        <div class="absolute inset-y-0 right-0 w-80 max-w-[85vw] p-6 overflow-y-auto" style="background: var(--lp-surface);"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
            <div class="flex items-center justify-between mb-6">
                <span class="font-display font-bold text-lg" style="color: var(--lp-ink);">{{ $platform['app_name'] ?? 'Sikad Pro' }}</span>
                <button type="button" class="inline-flex items-center justify-center w-11 h-11 rounded-lg" style="color: var(--lp-ink);" @click="open = false" aria-label="Tutup menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <nav class="flex flex-col gap-1" aria-label="Navigasi mobile">
                @foreach($nav as $item)
                    <a href="{{ $item['href'] }}" class="py-3 text-base font-medium border-b" style="color: var(--lp-ink); border-color: var(--lp-border);" @click="open = false">{{ $item['label'] }}</a>
                @endforeach
            </nav>
            <div class="flex flex-col gap-3 mt-6">
                <a href="{{ $demoHref }}" class="lp-btn w-full">Request Demo</a>
                <a href="{{ route('admin.login') }}" class="lp-btn-ghost lp-btn w-full">Login</a>
            </div>
        </div>
    </div>
</header>
