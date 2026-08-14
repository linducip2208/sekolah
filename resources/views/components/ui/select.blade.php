@props(['label' => null, 'name' => null, 'error' => null, 'required' => false, 'options' => []])
@php $id = $attributes->get('id') ?? ($name ? 'f_' . str_replace(['[',']','.'], '_', $name) : null); @endphp
<div>
    @if($label)
        <label for="{{ $id }}" class="label">
            {{ $label }}@if($required)<span class="req" aria-hidden="true"> *</span>@endif
        </label>
    @endif
    <select name="{{ $name }}" id="{{ $id }}" {{ $required ? 'required' : '' }}
        {{ $error ? 'aria-invalid="true" aria-describedby="'.($id ? $id.'_error' : 'error').'"' : '' }}
        {{ $attributes->merge(['class' => 'select' . ($error ? ' is-invalid' : '')]) }}>
        {{ $slot }}
    </select>
    @if($error)<p class="form-error" id="{{ $id }}_error">{{ $error }}</p>@endif
</div>
