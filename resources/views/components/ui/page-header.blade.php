@props([
    'title',
    'subtitle' => null,
    'backHref' => null,
    'backLabel' => 'Kembali',
])
<div {{ $attributes->merge(['class' => 'mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        @if($backHref)
            <a href="{{ $backHref }}" class="inline-flex items-center gap-1 text-xs font-medium text-[var(--color-text-muted)] hover:text-[var(--color-text)] mb-1.5 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                {{ $backLabel }}
            </a>
        @endif
        <h1 class="page-title">{{ $title }}</h1>
        @if($subtitle)<p class="text-sm text-[var(--color-text-secondary)] mt-1 max-w-2xl">{{ $subtitle }}</p>@endif
    </div>
    @if($slot->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2 flex-shrink-0">{{ $slot }}</div>
    @endif
</div>
