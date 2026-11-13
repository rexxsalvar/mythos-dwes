@props(['href', 'title', 'icon', 'action'])
<a {{ $attributes->class(['feature-card']) }} href="{{ $href }}">
    <span class="entry-icon"><x-icon :name="$icon" /></span>
    <div class="entry-copy">
        <h2>{{ $title }}</h2>
        <p>{{ $slot }}</p>
        <span class="text-link">{{ $action }}</span>
    </div>
    <x-icon />
</a>
