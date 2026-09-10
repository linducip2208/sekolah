@props([
    'label',
    'value' => null,
    'tone' => 'primary',       // primary | success | warning | danger | info
    'href' => null,
    'delta' => null,           // e.g. '+3.2%' — ditampilkan dengan arah semantik
    'deltaTone' => null,       // success|danger; default: positif=success
    'icon' => null,
    'hint' => null,
])
@php
    $toneColor = "var(--color-{$tone})";
    $toneSoft = "var(--color-{$tone}-soft)";
    $autoDeltaTone = $deltaTone ?? (str_starts_with((string) $delta, '-') ? 'danger' : 'success');
@endphp
<{{ $href ? 'a href="' . $href . '"' : 'div' }} {{ $attributes->merge(['class' => 'card card-pad card-hover block group']) }}>
    <div class="flex items-start justify-between gap-2">
        <p class="text-[12px] font-medium text-[var(--color-text-secondary)] leading-snug min-h-[2rem]">{{ $label }}</p>
        @if($icon)
            <span class="h-8 w-8 rounded-lg flex items-center justify-center flex-shrink-0 transition-transform group-hover:scale-105" style="background: {{ $toneSoft }}; color: {{ $toneColor }};" aria-hidden="true">
                <x-ui.icon :name="$icon" class="w-4 h-4" />
            </span>
        @endif
    </div>
    <div class="mt-1 flex items-baseline gap-2 flex-wrap">
        <span class="text-2xl font-extrabold tracking-tight tabular-nums" style="color: var(--color-text);">{{ $value ?? '—' }}</span>
        @if($delta !== null && $delta !== '')
            <span class="text-[11px] font-bold tabular-nums px-1.5 py-0.5 rounded-md"
                  style="background: var(--color-{{ $autoDeltaTone }}-soft); color: var(--color-{{ $autoDeltaTone }});">{{ $delta }}</span>
        @endif
    </div>
    @if($hint)<p class="mt-1 text-[11px] text-[var(--color-text-muted)]">{{ $hint }}</p>@endif
</{{ $href ? 'a' : 'div' }}>
