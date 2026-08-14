{{-- One-time popup: source code dijual (whitelabel-driven). Self-contained, uses lp tokens. --}}
@php $p = $platform ?? app(\App\Services\PlatformSettingsService::class)->all(); @endphp
@if(!empty($p['popup_enabled']))
<div id="lp-popup" style="display:none;" class="fixed inset-0 z-[100] items-center justify-center p-4" aria-hidden="false">
    <div class="absolute inset-0" style="background: rgba(15,23,42,.6);"></div>
    <div class="relative max-w-md w-full rounded-2xl overflow-hidden" style="background: var(--lp-surface); box-shadow: 0 24px 64px -16px rgba(0,0,0,.4);">
        <button type="button" id="lp-popup-close" aria-label="Tutup" class="absolute top-3 right-3 w-9 h-9 flex items-center justify-center rounded-full text-[var(--lp-muted)] hover:text-[var(--lp-ink)] hover:bg-[var(--lp-accent-soft)] transition">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="px-8 pt-8 pb-7 text-center">
            <p class="lp-kicker mb-3">{{ $p['app_name'] ?? 'Sikad Pro' }}</p>
            <h3 class="font-display text-2xl font-bold mb-3" style="color: var(--lp-ink);">{{ $p['popup_title'] ?? 'Source Code Dijual' }}</h3>
            <p class="text-sm leading-relaxed" style="color: var(--lp-muted);">{{ $p['popup_message'] ?? 'Aplikasi ini tersedia untuk dibeli, lengkap dengan source code dan dokumentasi.' }}</p>
            <div class="my-6 py-4 border-y" style="border-color: var(--lp-border);">
                <div class="text-xs uppercase tracking-wide mb-1" style="color: var(--lp-muted);">Hubungi</div>
                <div class="text-xl font-bold" style="color: var(--lp-primary);">
                    <a href="tel:{{ $p['popup_phone'] ?? '' }}" class="hover:underline">{{ $p['popup_phone'] ?? '' }}</a>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                @if(!empty($p['popup_whatsapp_link']))
                    <a href="{{ $p['popup_whatsapp_link'] }}" target="_blank" rel="noopener" class="lp-btn lp-btn-accent">WhatsApp</a>
                @endif
                <a href="tel:{{ $p['popup_phone'] ?? '' }}" class="lp-btn lp-btn-secondary">{{ $p['popup_cta_text'] ?? 'Hubungi Sekarang' }}</a>
            </div>
            <button type="button" id="lp-popup-dismiss" class="mt-5 text-xs underline" style="color: var(--lp-muted);">Jangan tampilkan lagi</button>
        </div>
    </div>
</div>
<script>
(function () {
    var KEY = 'lp_popup_seen_v{{ $p['cache_version'] ?? 1 }}';
    var el = document.getElementById('lp-popup');
    if (!el || localStorage.getItem(KEY) === '1') return;
    setTimeout(function () { el.style.display = 'flex'; document.body.style.overflow = 'hidden'; }, 1200);
    function close(persistent) {
        el.style.display = 'none';
        document.body.style.overflow = '';
        if (persistent) localStorage.setItem(KEY, '1');
    }
    document.getElementById('lp-popup-close').addEventListener('click', function () { close(true); });
    document.getElementById('lp-popup-dismiss').addEventListener('click', function () { close(true); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(true); });
})();
</script>
@endif
