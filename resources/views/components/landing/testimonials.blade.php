@props(['theme' => [], 'landing' => []])
<section class="lp-section lp-bg-surface">
    <div class="lp-container">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <p class="lp-kicker mb-3">Testimoni</p>
            <h2 class="lp-title text-3xl sm:text-4xl">Dipercaya oleh institusi pendidikan.</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach($landing['testimonials'] as $t)
                <figure class="lp-card lp-card-{{ $theme['style']['card'] }} p-6 flex flex-col reveal">
                    <svg class="w-7 h-7 mb-4" style="color: var(--lp-accent);" viewBox="0 0 24 24" fill="currentColor"><path d="M9.5 5C6.5 7 5 9.5 5 13v6h6v-6H8c0-2 .8-3.5 2.5-4.7L9.5 5zm9 0c-3 2-4.5 4.5-4.5 8v6h6v-6h-3c0-2 .8-3.5 2.5-4.7L18.5 5z"/></svg>
                    <blockquote class="text-sm leading-relaxed flex-1" style="color: var(--lp-muted);">{{ $t['quote'] }}</blockquote>
                    <figcaption class="mt-5 pt-4 border-t" style="border-color: var(--lp-border);">
                        <div class="font-semibold text-sm" style="color: var(--lp-ink);">{{ $t['name'] }}</div>
                        <div class="text-xs mt-0.5" style="color: var(--lp-muted);">{{ $t['role'] }}</div>
                        @if(!empty($t['placeholder']))
                            <div class="text-[11px] mt-2 italic" style="color: var(--lp-muted); opacity: .7;">— placeholder, dapat diganti dari data —</div>
                        @endif
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
