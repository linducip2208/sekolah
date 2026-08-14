@props(['label' => null, 'name' => null, 'value' => null, 'hint' => null, 'error' => null, 'required' => false, 'rows' => 3])
@php $id = $attributes->get('id') ?? ($name ? 'f_' . str_replace(['[',']','.'], '_', $name) : null); @endphp
<div>
    @if($label)
        <label for="{{ $id }}" class="label">
            {{ $label }}@if($required)<span class="req" aria-hidden="true"> *</span>@endif
        </label>
    @endif
    <textarea name="{{ $name }}" id="{{ $id }}" rows="{{ $rows }}" {{ $required ? 'required' : '' }}
        {{ $error ? 'aria-invalid="true" aria-describedby="'.($id ? $id.'_error' : 'error').'"' : '' }}
        {{ $attributes->merge(['class' => 'textarea' . ($error ? ' is-invalid' : '')]) }}>{{ old($name, $value) }}</textarea>
    @if($hint)<p class="form-hint" id="{{ $id }}_hint">{{ $hint }}</p>@endif
    @if($error)<p class="form-error" id="{{ $id }}_error">{{ $error }}</p>@endif
</div>
