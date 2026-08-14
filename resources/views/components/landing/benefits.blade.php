@props(['theme' => [], 'landing' => []])
<section class="lp-section" style="background: var(--lp-bg);">
    <div class="lp-container grid lg:grid-cols-2 gap-12 items-center">
        <div class="max-w-xl">
            <p class="lp-kicker mb-3">Manfaat</p>
            <h2 class="lp-title text-3xl sm:text-4xl">Bukan sekadar fitur — hasil nyata.</h2>
            <p class="lp-lead mt-4">Platform mengurangi beban administrasi dan memberi visibilitas yang selama ini tersebar di banyak tempat.</p>
        </div>
        <div class="grid sm:grid-cols-2 gap-6">
            @foreach($landing['benefits'] as $b)
                <div class="lp-card lp-card-{{ $theme['style']['card'] }} p-6 reveal">
                    <h3 class="font-semibold text-lg" style="color: var(--lp-ink);">{{ $b['title'] }}</h3>
                    <p class="text-sm mt-2 leading-relaxed" style="color: var(--lp-muted);">{{ $b['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
