{{-- One-time popup: source code dijual. Whitelabel-driven. --}}
@php $p = $platform ?? app(\App\Services\PlatformSettingsService::class)->all(); @endphp
@if(!empty($p['popup_enabled']))
<div id="elite-popup" style="display:none;" class="fixed inset-0 z-[100] items-center justify-center bg-black/55 backdrop-blur-sm p-4">
    <div class="relative max-w-lg w-full" style="font-family: 'Inter', sans-serif;">
        <div class="paper deco-frame mx-2" style="background: var(--c-paper); border: 1px solid var(--c-accent);">
            <button type="button" id="elite-popup-close" aria-label="Tutup" class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-black/5 rounded-full transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <div class="px-8 pt-8 pb-7 text-center">
                <div class="ornament-center"></div>
                <div class="elite-kicker mb-3">{{ $p['app_name'] ?? 'Sikad Pro' }} · Notice</div>
                <h3 class="elite-h2 text-3xl ink-primary mb-2">{{ $p['popup_title'] ?? 'Source Code Dijual' }}</h3>
                <div class="elite-rule mx-auto my-4"></div>
                <p class="font-serif text-lg leading-relaxed" style="color:#3d362f;">
                    {{ $p['popup_message'] ?? 'Aplikasi ini tersedia untuk dibeli, lengkap dengan source code dan dokumentasi.' }}
                </p>
                <div class="my-6 py-4 border-y border-rule">
                    <div class="elite-kicker mb-1">Hubungi</div>
                    <div class="elite-h3 text-2xl ink-secondary">
                        <a href="tel:{{ $p['popup_phone'] ?? '' }}" class="hover:underline">{{ $p['popup_phone'] ?? '' }}</a>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    @if(!empty($p['popup_whatsapp_link']))
                        <a href="{{ $p['popup_whatsapp_link'] }}" target="_blank" rel="noopener" class="btn-elite-gold">
                            <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            WhatsApp
                        </a>
                    @endif
                    <a href="tel:{{ $p['popup_phone'] ?? '' }}" class="btn-elite-ghost">{{ $p['popup_cta_text'] ?? 'Hubungi Sekarang' }}</a>
                </div>
                <button type="button" id="elite-popup-dismiss" class="mt-5 text-xs text-gray-500 hover:text-gray-800 underline">Jangan tampilkan lagi</button>
            </div>
        </div>
    </div>
</div>
<script>
(function(){
    var KEY = 'elite_popup_seen_v{{ $p['cache_version'] ?? 1 }}';
    var el  = document.getElementById('elite-popup');
    if (!el) return;
    if (localStorage.getItem(KEY) === '1') return;

    setTimeout(function(){
        el.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }, 1200);

    function close(persistent){
        el.style.display = 'none';
        document.body.style.overflow = '';
        if (persistent) localStorage.setItem(KEY, '1');
    }

    document.getElementById('elite-popup-close').addEventListener('click', function(){ close(true); });
    document.getElementById('elite-popup-dismiss').addEventListener('click', function(){ close(true); });
    el.addEventListener('click', function(e){ if (e.target === el) close(true); });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape') close(true); });
})();
</script>
@endif
