@props(['name' => 'A', 'size' => null, 'src' => null])
@php $cls = 'avatar' . ($size ? ' avatar-'.$size : ''); @endphp
<span {{ $attributes->merge(['class' => $cls]) }}>
    @if($src)<img src="{{ $src }}" alt="">@else{{ strtoupper(mb_substr($name, 0, 1)) }}@endif
</span>
