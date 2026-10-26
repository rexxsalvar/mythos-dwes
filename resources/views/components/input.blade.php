@props(['name', 'type' => 'text', 'value' => '', 'id' => null])
@php($fieldId = $id ?? $name)
<input id="{{ $fieldId }}" name="{{ $name }}" type="{{ $type }}"
    @if($type !== 'password') value="{{ old($name, $value) }}" @endif
    {{ $attributes->class(['field-control', 'field-invalid' => $errors->has($name)]) }}
    @if($errors->has($name)) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @endif>
@error($name)<p id="{{ $fieldId }}-error" class="field-error" role="alert">{{ $message }}</p>@enderror
