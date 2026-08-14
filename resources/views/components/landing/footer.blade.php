@props(['theme' => []])
<footer style="background: var(--lp-ink);">
    <div class="lp-container py-14">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8">
            <div class="col-span-2">
                <div class="flex items-center gap-2.5 mb-4">
                    @if(!empty($platform['logo_dark_url']))
                        <img src="{{ $platform['logo_dark_url'] }}" alt="" class="h-9 w-auto">
                    @elseif(!empty($platform['logo_url']))
                        <img src="{{ $platform['logo_url'] }}" alt="" class="h-9 w-auto brightness-0 invert">
                    @else
                        <span class="h-9 w-9 rounded-lg flex items-center justify-center font-bold" style="background: var(--lp-accent); color: #fff;">{{ Str::upper(Str::substr($platform['app_name'] ?? 'S', 0, 1)) }}</span>
                    @endif
                    <span class="font-display font-bold text-lg" style="color: #fff;">{{ $platform['app_name'] ?? 'Sikad Pro' }}</span>
                </div>
                <p class="text-sm leading-relaxed max-w-xs" style="color: rgba(255,255,255,.6);">{{ $platform['description'] ?? '' }}</p>
            </div>

            <div>
                <h4 class="text-sm font-semibold mb-3" style="color: #fff;">Produk</h4>
                <ul class="space-y-2 text-sm" style="color: rgba(255,255,255,.6);">
                    <li><a href="#fitur" class="hover:text-white">Fitur</a></li>
                    <li><a href="#solusi" class="hover:text-white">Solusi</a></li>
                    <li><a href="{{ route('public.pricing') }}" class="hover:text-white">Harga</a></li>
                    <li><a href="{{ route('admin.login') }}" class="hover:text-white">Akun Demo</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold mb-3" style="color: #fff;">Sumber Daya</h4>
                <ul class="space-y-2 text-sm" style="color: rgba(255,255,255,.6);">
                    <li><a href="/docs" class="hover:text-white">Buku Panduan</a></li>
                    <li><a href="{{ route('api.docs') }}" class="hover:text-white">Dokumentasi API</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white">Blog</a></li>
                    <li><a href="/sitemap.xml" class="hover:text-white">Peta Situs</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold mb-3" style="color: #fff;">Perusahaan</h4>
                <ul class="space-y-2 text-sm" style="color: rgba(255,255,255,.6);">
                    <li><a href="{{ route('super.login') }}" class="hover:text-white">Operator Platform</a></li>
                    <li><a href="{{ route('portal.invoices') }}" class="hover:text-white">Portal Orang Tua</a></li>
                    <li><a href="mailto:{{ $platform['contact_email'] ?? '' }}" class="hover:text-white">Kontak</a></li>
                    @if(!empty($platform['contact_phone']))<li><a href="tel:{{ $platform['contact_phone'] }}" class="hover:text-white">{{ $platform['contact_phone'] }}</a></li>@endif
                </ul>
            </div>
        </div>

        <div class="mt-12 pt-6 border-t flex flex-col sm:flex-row items-center justify-between gap-4 text-sm" style="border-color: rgba(255,255,255,.15); color: rgba(255,255,255,.5);">
            <div>&copy; {{ now()->year }} {{ $platform['app_name'] ?? 'Sikad Pro' }}. Seluruh hak cipta.</div>
            <div class="flex items-center gap-6">
                <a href="{{ route('public.pricing') }}" class="hover:text-white">Privasi</a>
                <a href="{{ route('public.pricing') }}" class="hover:text-white">Ketentuan</a>
                @if(!empty($platform['whatsapp_link']))<a href="{{ $platform['whatsapp_link'] }}" target="_blank" rel="noopener" class="hover:text-white">WhatsApp</a>@endif
            </div>
        </div>
    </div>
</footer>
