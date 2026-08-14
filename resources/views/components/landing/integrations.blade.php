@props(['theme' => [], 'landing' => []])
<section class="lp-section" style="background: var(--lp-bg);">
    <div class="lp-container grid lg:grid-cols-2 gap-12 items-center">
        <div class="max-w-xl">
            <p class="lp-kicker mb-3">Integrasi</p>
            <h2 class="lp-title text-3xl sm:text-4xl">Anda memegang kendali penuh atas vendor Anda.</h2>
            <p class="lp-lead mt-4">Prinsip <em>bring your own keys</em> — setiap integrasi pihak ketiga dapat ditukar kapan saja tanpa menunggu rilis.</p>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            @foreach($landing['integration'] as $it)
                <div class="lp-card lp-card-{{ $theme['style']['card'] }} p-5 reveal">
                    <h3 class="font-semibold" style="color: var(--lp-ink);">{{ $it['title'] }}</h3>
                    <p class="text-sm mt-1.5 leading-relaxed" style="color: var(--lp-muted);">{{ $it['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
