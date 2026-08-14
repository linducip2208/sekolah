@props(['theme' => [], 'landing' => []])
@php
    $icons = [
        'M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 1v2h10V6H7zm0 4v2h10v-2H7zm0 4v2h7v-2H7z',
        'M13 10V3L4 14h7v7l9-11h-7z',
        'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.9 2A4 4 0 003 15z',
        'M12 2L3 7v6c0 5 3.5 8 9 9 5.5-1 9-4 9-9V7l-9-5zm0 2.18L18 7v6c0 3.5-2.3 6-6 7.3C8.3 19 6 16.5 6 13V7l6-2.82z',
    ];
@endphp
<section class="lp-section lp-bg-subtle">
    <div class="lp-container">
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($landing['valueProps'] as $i => $vp)
                <div class="lp-card lp-card-{{ $theme['style']['card'] }} p-6 reveal">
                    <div class="h-11 w-11 rounded-lg flex items-center justify-center mb-4" style="background: var(--lp-accent-soft); color: var(--lp-primary);">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$i] ?? $icons[0] }}"/></svg>
                    </div>
                    <h3 class="font-semibold text-lg" style="color: var(--lp-ink);">{{ $vp['title'] }}</h3>
                    <p class="text-sm mt-2 leading-relaxed" style="color: var(--lp-muted);">{{ $vp['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
