@props(['variant' => 'primary', 'type' => null, 'size' => null, 'href' => null, 'icon' => null])

@php
    $map = [
        'primary'   => 'btn',
        'secondary' => 'btn btn-secondary',
        'ghost'     => 'btn btn-ghost',
        'success'   => 'btn btn-success',
        'warning'   => 'btn btn-warning',
        'danger'    => 'btn btn-danger',
        'accent'    => 'btn btn-accent',
    ];
    $classes = $map[$variant] ?? 'btn';
    if ($size) $classes .= " btn-{$size}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)<x-ui.icon :name="$icon" />@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type ?? 'button' }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)<x-ui.icon :name="$icon" />@endif
        {{ $slot }}
    </button>
@endif
