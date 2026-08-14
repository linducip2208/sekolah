@props(['padding' => true, 'hover' => false])
@php
    $classes = 'card' . ($padding ? ' card-pad' : '') . ($hover ? ' card-hover' : '');
@endphp
<div {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</div>
