@props(['label' => null, 'name' => null, 'type' => 'text', 'value' => null, 'hint' => null, 'error' => null, 'required' => false])
@php $id = $attributes->get('id') ?? ($name ? 'f_' . str_replace(['[',']','.'], '_', $name) : null); @endphp
<div {{ $attributes->except(['id', 'class', 'value', 'required', 'type'])->merge(['class' => '']) }}>
    @if($label)
        <label for="{{ $id }}" class="label">
            {{ $label }}@if($required)<span class="req" aria-hidden="true"> *</span>@endif
        </label>
    @endif
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" value="{{ old($name, $value) }}"
        {{ $required ? 'required' : '' }}
        {{ $error ? 'aria-invalid="true" aria-describedby="'.($id ? $id.'_error' : 'error').'"' : '' }}
        {{ $attributes->merge(['class' => 'input' . ($error ? ' is-invalid' : '')]) }}>
    @if($hint)<p class="form-hint" id="{{ $id }}_hint">{{ $hint }}</p>@endif
    @if($error)<p class="form-error" id="{{ $id }}_error">{{ $error }}</p>@endif
</div>
