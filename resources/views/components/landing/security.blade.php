@props(['theme' => [], 'landing' => []])
<section class="lp-section" style="background: var(--lp-surface);">
    <div class="lp-container">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <p class="lp-kicker mb-3">Keamanan & Keandalan</p>
            <h2 class="lp-title text-3xl sm:text-4xl">Dibangun dengan standar keamanan enterprise.</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($landing['security'] as $s)
                <div class="flex items-start gap-3 p-5 reveal">
                    <div class="h-9 w-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background: var(--lp-accent-soft); color: var(--lp-primary);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold" style="color: var(--lp-ink);">{{ $s['title'] }}</h3>
                        <p class="text-sm mt-1 leading-relaxed" style="color: var(--lp-muted);">{{ $s['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
