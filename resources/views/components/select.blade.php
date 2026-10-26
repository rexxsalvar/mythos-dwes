@props(['name', 'id' => null])
@php($fieldId = $id ?? $name)
<select id="{{ $fieldId }}" name="{{ $name }}"
    {{ $attributes->class(['field-control', 'field-invalid' => $errors->has($name)]) }}
    @if($errors->has($name)) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @endif>{{ $slot }}</select>
@error($name)<p id="{{ $fieldId }}-error" class="field-error" role="alert">{{ $message }}</p>@enderror
