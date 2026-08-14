@props(['theme' => [], 'landing' => []])
@php
    $heroStyle = $theme['style']['hero'] ?? 'split';
    $demoHref = $platform['whatsapp_link'] ?? route('public.pricing');
    $shot = $landing['preview'][0]['img'] ?? null;
    $shotAlt = $landing['preview'][0]['alt'] ?? 'Tampilan dashboard';
    $centered = $heroStyle === 'centered';
@endphp

<section id="beranda" class="lp-hero">
    <div class="lp-hero-pattern {{ $theme['style']['pattern'] ?? 'none' }}" aria-hidden="true"></div>
    <div class="lp-container relative py-16 lg:py-24">
        <div class="{{ $centered ? 'text-center max-w-3xl mx-auto' : 'grid lg:grid-cols-2 gap-12 items-center' }}">
            <div class="{{ $centered ? '' : 'max-w-xl' }}">
                <p class="lp-kicker mb-4">Platform Manajemen Sekolah</p>
                <h1 class="lp-title text-4xl sm:text-5xl {{ $centered ? 'mx-auto' : '' }} lg:text-6xl">
                    Kelola seluruh operasional sekolah dalam satu platform.
                </h1>
                <p class="lp-lead mt-6 {{ $centered ? 'mx-auto max-w-2xl' : 'max-w-xl' }}">
                    Akademik, siswa, keuangan, PPDB, HR, komunikasi, dan laporan — terintegrasi dalam satu sistem untuk sekolah, yayasan, dan pesantren.
                </p>
                <div class="flex flex-wrap gap-3 mt-8 {{ $centered ? 'justify-center' : '' }}">
                    <a href="{{ $demoHref }}" class="lp-btn">Request Demo</a>
                    <a href="#fitur" class="lp-btn lp-btn-secondary">Jelajahi Fitur</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 mt-12 {{ $centered ? 'max-w-3xl mx-auto' : 'max-w-xl' }}">
                    @foreach($landing['stats'] as $stat)
                        <div>
                            <div class="font-display text-2xl font-bold" style="color: var(--lp-primary);">{{ $stat['value'] }}</div>
                            <div class="text-sm mt-1" style="color: var(--lp-muted);">{{ $stat['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if(!$centered)
            <div class="relative reveal visible">
                <div class="rounded-xl overflow-hidden shadow-2xl border" style="border-color: var(--lp-border);">
                    {{-- Browser chrome --}}
                    <div class="flex items-center gap-1.5 px-4 py-3" style="background: var(--lp-surface); border-bottom: 1px solid var(--lp-border);">
                        <span class="w-3 h-3 rounded-full bg-[#f87171]"></span>
                        <span class="w-3 h-3 rounded-full bg-[#fbbf24]"></span>
                        <span class="w-3 h-3 rounded-full bg-[#34d399]"></span>
                        <span class="ml-3 flex-1 text-xs truncate px-3 py-1 rounded-md" style="background: var(--lp-surface-subtle); color: var(--lp-muted);">{{ parse_url(url('/'), PHP_URL_HOST) }}/dashboard</span>
                    </div>
                    @if($shot)
                        <img src="{{ $shot }}" alt="{{ $shotAlt }}" loading="lazy" class="w-full h-auto">
                    @endif
                </div>
            </div>
            @endif
        </div>

        @if($centered)
        <div class="mt-12 max-w-4xl mx-auto reveal">
            <div class="rounded-xl overflow-hidden shadow-2xl border" style="border-color: var(--lp-border);">
                <div class="flex items-center gap-1.5 px-4 py-3" style="background: var(--lp-surface); border-bottom: 1px solid var(--lp-border);">
                    <span class="w-3 h-3 rounded-full bg-[#f87171]"></span>
                    <span class="w-3 h-3 rounded-full bg-[#fbbf24]"></span>
                    <span class="w-3 h-3 rounded-full bg-[#34d399]"></span>
                    <span class="ml-3 flex-1 text-xs truncate px-3 py-1 rounded-md" style="background: var(--lp-surface-subtle); color: var(--lp-muted);">{{ parse_url(url('/'), PHP_URL_HOST) }}/dashboard</span>
                </div>
                @if($shot)<img src="{{ $shot }}" alt="{{ $shotAlt }}" loading="lazy" class="w-full h-auto">@endif
            </div>
        </div>
        @endif
    </div>
</section>
