@props([
    'title',
    'description' => null,
    'tone' => 'warning',   // danger | warning | info | success
    'ctaLabel' => null,
    'ctaHref' => null,
    'count' => null,
])
<div {{ $attributes->merge(['class' => 'flex items-center gap-3 px-4 py-3.5 rounded-xl border transition']) }}
     style="background: var(--color-{{ $tone }}-soft); border-color: transparent;">
    <div class="h-9 w-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background: var(--color-{{ $tone }}); color: #fff;" aria-hidden="true">
        <x-ui.icon :name="$tone === 'danger' ? 'alert' : ($tone === 'success' ? 'check' : 'clock')" class="w-5 h-5" />
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold" style="color: var(--color-text);">
            {{ $title }}@if($count !== null)
                <span class="badge badge-{{ $tone }} ml-1.5">{{ $count }}</span>
            @endif
        </p>
        @if($description)<p class="text-xs mt-0.5" style="color: var(--color-text-secondary);">{{ $description }}</p>@endif
    </div>
    @if($ctaLabel && $ctaHref && $slot->isEmpty())
        <a href="{{ $ctaHref }}" class="btn btn-sm flex-shrink-0 whitespace-nowrap" style="background: var(--color-{{ $tone }});">{{ $ctaLabel }}</a>
    @endif
    @if($slot->isNotEmpty()){{ $slot }}@endif
</div>
