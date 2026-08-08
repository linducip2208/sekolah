@php $p = $platform ?? app(\App\Services\PlatformSettingsService::class)->all(); @endphp
<footer class="bg-primary text-white/90 mt-12 sm:mt-20 lg:mt-24" style="background: var(--c-primary);">
    <div class="max-w-7xl mx-auto px-3 sm:px-5 lg:px-6 py-8 sm:py-12 lg:py-16">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-6 sm:gap-8 lg:gap-10">
            <div class="sm:col-span-2 lg:col-span-4">
                <div class="flex items-center gap-3 mb-4 sm:mb-5">
                    @if(!empty($p['logo_dark_url']))
                        <img src="{{ $p['logo_dark_url'] }}" alt="" class="h-10 sm:h-12 w-auto">
                    @elseif(!empty($p['logo_url']))
                        <img src="{{ $p['logo_url'] }}" alt="" class="h-10 sm:h-12 w-auto brightness-0 invert opacity-90">
                    @else
                        <div class="crest-mark" style="border-color: var(--c-accent);">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 2 7l10 5 10-5-10-5zm0 7L2 14l10 5 10-5-10-5z"/></svg>
                        </div>
                    @endif
                    <div>
                        <div class="elite-h3 text-xl sm:text-2xl text-white">{{ $p['app_name'] ?? 'Sikad Pro' }}</div>
                        <div class="elite-kicker text-[.55rem] sm:text-[.6rem] text-white/60 mt-1" style="color: rgba(255,255,255,.55);">Est. {{ $p['established_year'] ?? '1890' }}</div>
                    </div>
                </div>
                <p class="font-serif text-base sm:text-lg leading-relaxed text-white/75">{{ $p['description'] ?? '' }}</p>
                <p class="font-script italic text-lg sm:text-xl mt-4 sm:mt-5" style="color: var(--c-accent);">"{{ $p['motto_latin'] ?? '' }}" — {{ $p['motto_translated'] ?? '' }}</p>
            </div>

            <div class="lg:col-span-2">
                <h4 class="elite-kicker mb-3 sm:mb-4" style="color: var(--c-accent);">Akademi</h4>
                <ul class="space-y-2 font-serif text-base text-white/80">
                    <li><a href="/#fitur" class="hover:text-white py-1 inline-block">Tentang</a></li>
                    <li><a href="/#modul" class="hover:text-white py-1 inline-block">Program</a></li>
                    <li><a href="/#harga" class="hover:text-white py-1 inline-block">Penerimaan</a></li>
                    <li><a href="/docs" class="hover:text-white py-1 inline-block">Panduan</a></li>
                </ul>
            </div>

            <div class="lg:col-span-3">
                <h4 class="elite-kicker mb-3 sm:mb-4" style="color: var(--c-accent);">Untuk Sekolah</h4>
                <ul class="space-y-2 font-serif text-base text-white/80">
                    <li><a href="{{ route('admin.login') }}" class="hover:text-white py-1 inline-block">Login Administrator</a></li>
                    <li><a href="{{ route('portal.invoices') }}" class="hover:text-white py-1 inline-block">Portal Orang Tua</a></li>
                    <li><a href="{{ route('super.login') }}" class="hover:text-white py-1 inline-block">Operator Platform</a></li>
                    <li><a href="/sitemap.xml" class="hover:text-white py-1 inline-block">Peta Situs</a></li>
                </ul>
            </div>

            <div class="sm:col-span-2 lg:col-span-3">
                <h4 class="elite-kicker mb-3 sm:mb-4" style="color: var(--c-accent);">Korespondensi</h4>
                <address class="not-italic font-serif text-base text-white/80 leading-relaxed">
                    {{ $p['address_line1'] ?? '' }}<br>
                    {{ $p['address_line2'] ?? '' }}
                </address>
                <div class="mt-3 sm:mt-4 space-y-1.5 text-sm text-white/75">
                    @if(!empty($p['contact_phone']))<div>Telp: <a href="tel:{{ $p['contact_phone'] }}" class="hover:text-white">{{ $p['contact_phone'] }}</a></div>@endif
                    @if(!empty($p['contact_email']))<div>Email: <a href="mailto:{{ $p['contact_email'] }}" class="hover:text-white break-all">{{ $p['contact_email'] }}</a></div>@endif
                </div>
                <div class="flex flex-wrap gap-2 sm:gap-3 mt-4 sm:mt-5">
                    @foreach(['social_facebook'=>'F','social_instagram'=>'IG','social_youtube'=>'YT','social_linkedin'=>'in'] as $key=>$initial)
                        @if(!empty($p[$key]))
                            <a href="{{ $p[$key] }}" target="_blank" rel="noopener" class="w-10 h-10 sm:w-9 sm:h-9 flex items-center justify-center border border-white/30 hover:border-[var(--c-accent)] hover:text-[var(--c-accent)] text-xs font-semibold transition">{{ $initial }}</a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <div class="border-t border-white/15 mt-8 sm:mt-12 pt-5 sm:pt-6 flex flex-col sm:flex-row justify-between items-center gap-2 sm:gap-3 text-[11px] sm:text-xs text-white/55 text-center sm:text-left" style="font-family:'Inter',sans-serif;letter-spacing:.08em;">
            <div>&copy; {{ now()->year }} {{ $p['app_name'] ?? 'Sikad Pro' }}. {{ $p['footer_disclaimer'] ?? '' }}</div>
            <div class="font-script italic" style="color: var(--c-accent); font-size: 1rem;">Floreat Schola.</div>
        </div>
    </div>
</footer>
