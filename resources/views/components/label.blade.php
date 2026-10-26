@props(['for'])
<label for="{{ $for }}" {{ $attributes->class(['field-label']) }}>{{ $slot }}</label>
