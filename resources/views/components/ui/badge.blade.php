@props(['variant' => 'default'])
@php
    $map = ['default' => 'badge', 'success' => 'badge badge-success', 'warning' => 'badge badge-warning', 'danger' => 'badge badge-danger', 'info' => 'badge badge-info', 'accent' => 'badge badge-accent', 'primary' => 'badge badge-primary'];
@endphp
<span {{ $attributes->merge(['class' => $map[$variant] ?? 'badge']) }}>{{ $slot }}</span>
