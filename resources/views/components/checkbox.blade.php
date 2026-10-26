@props(['name', 'checked' => false])
<div class="checkbox-field">
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" id="{{ $name }}" name="{{ $name }}" value="1" @checked(old($name, $checked)) {{ $attributes }}>
    <x-label :for="$name">{{ $slot }}</x-label>
</div>
@error($name)<p class="field-error" role="alert">{{ $message }}</p>@enderror
