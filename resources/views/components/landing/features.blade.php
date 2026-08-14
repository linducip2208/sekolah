@props(['theme' => [], 'landing' => []])
<section class="lp-section" id="fitur" style="background: var(--lp-bg);">
    <div class="lp-container">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <p class="lp-kicker mb-3">Fitur</p>
            <h2 class="lp-title text-3xl sm:text-4xl">Modul lengkap untuk setiap bagian sekolah.</h2>
            <p class="lp-lead mt-4">Empat puluh lima modul terintegrasi, dikelompokkan sesuai alur kerja nyata di sekolah.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($landing['features'] as $category => $items)
                <div class="lp-card lp-card-{{ $theme['style']['card'] }} p-6 reveal">
                    <h3 class="font-display font-bold text-lg mb-4" style="color: var(--lp-ink);">{{ $category }}</h3>
                    <ul class="space-y-2.5">
                        @foreach($items as $item)
                            <li class="flex items-start gap-2.5 text-sm leading-snug" style="color: var(--lp-muted);">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" style="color: var(--lp-accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</section>
