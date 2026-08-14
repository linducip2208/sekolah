@props(['theme' => [], 'landing' => []])
@php
    $iconMap = [
        'school' => 'school', 'device' => 'device', 'edit' => 'edit',
        'user' => 'user', 'bell' => 'bell', 'inbox' => 'inbox',
    ];
@endphp
<section class="lp-section" id="solusi" style="background: var(--lp-surface);">
    <div class="lp-container">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <p class="lp-kicker mb-3">Solusi per Peran</p>
            <h2 class="lp-title text-3xl sm:text-4xl">Dirancang untuk setiap peran di sekolah.</h2>
            <p class="lp-lead mt-4">Setiap pengguna melihat tampilan yang relevan dengan tugasnya — bukan sekadar menu yang disembunyikan.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($landing['solutions'] as $s)
                <div class="lp-card lp-card-{{ $theme['style']['card'] }} p-6 reveal">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="h-10 w-10 rounded-lg flex items-center justify-center" style="background: var(--lp-accent-soft); color: var(--lp-primary);">
                            <x-ui.icon :name="$iconMap[$s['icon']] ?? 'school'" class="w-5 h-5" />
                        </div>
                        <h3 class="font-semibold text-lg" style="color: var(--lp-ink);">{{ $s['role'] }}</h3>
                    </div>
                    <p class="text-sm leading-relaxed mb-4" style="color: var(--lp-muted);">{{ $s['desc'] }}</p>
                    <ul class="space-y-1.5">
                        @foreach($s['bullets'] as $b)
                            <li class="flex items-start gap-2 text-sm" style="color: var(--lp-muted);">
                                <span class="text-[var(--lp-accent)] mt-0.5">•</span>{{ $b }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</section>
