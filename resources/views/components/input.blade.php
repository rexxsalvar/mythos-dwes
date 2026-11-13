@props(['name', 'type' => 'text', 'value' => '', 'id' => null])
@php($fieldId = $id ?? $name)
@if($type === 'password')<div class="password-field">@endif
<input id="{{ $fieldId }}" name="{{ $name }}" type="{{ $type }}"
    @if($type !== 'password') value="{{ old($name, $value) }}" @endif
    {{ $attributes->class(['field-control', 'field-invalid' => $errors->has($name)]) }}
    @if($errors->has($name)) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @endif>
@if($type === 'password')
    <button class="password-toggle" type="button" aria-controls="{{ $fieldId }}" aria-label="Mostrar contraseña" aria-pressed="false" data-password-toggle hidden disabled><x-icon name="eye" /></button>
</div>
@endif
@error($name)<p id="{{ $fieldId }}-error" class="field-error" role="alert">{{ $message }}</p>@enderror
