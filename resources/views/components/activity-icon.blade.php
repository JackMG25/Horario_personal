@props([
    'name' => 'sparkles',
    'color' => 'violet',
    'size' => 'md',
])

@php
    $name = in_array($name, \App\Support\IconCatalog::keys(), true) ? $name : 'sparkles';
    $classes = \App\Support\IconCatalog::colorClasses();
    $colorClass = $classes[$color] ?? $classes['violet'];
    $sizes = [
        'sm' => 'h-9 w-9',
        'md' => 'h-11 w-11',
        'lg' => 'h-14 w-14',
    ];
    $iconSizes = [
        'sm' => 'h-4 w-4',
        'md' => 'h-5 w-5',
        'lg' => 'h-7 w-7',
    ];
@endphp

<div {{ $attributes->class([
    'flex shrink-0 items-center justify-center rounded-full',
    $sizes[$size] ?? $sizes['md'],
    $colorClass,
]) }}>
    <x-dynamic-component
        :component="'heroicon-o-'.$name"
        class="{{ $iconSizes[$size] ?? $iconSizes['md'] }}"
    />
</div>
