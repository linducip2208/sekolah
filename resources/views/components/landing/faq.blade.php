@props(['theme' => [], 'landing' => []])
<section class="lp-section lp-bg-surface" id="faq">
    <div class="lp-container max-w-3xl">
        <div class="text-center mb-12">
            <p class="lp-kicker mb-3">FAQ</p>
            <h2 class="lp-title text-3xl sm:text-4xl">Pertanyaan yang sering diajukan.</h2>
        </div>
        <div class="space-y-3">
            @foreach($landing['faqs'] as $faq)
                <details class="faq-item reveal">
                    <summary>
                        <span>{{ $faq['q'] }}</span>
                        <svg class="faq-icon w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    </summary>
                    <div class="faq-answer">{{ $faq['a'] }}</div>
                </details>
            @endforeach
        </div>
    </div>
</section>
