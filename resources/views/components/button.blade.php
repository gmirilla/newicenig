@props([
    'variant' => 'primary',
    'size' => 'md',
    'tag' => null,
])

@php
    $tag = $tag ?? (isset($attributes['href']) ? 'a' : 'button');

    $variants = [
        'primary' => 'bg-brand-600 text-white shadow-sm hover:bg-brand-700 focus-visible:outline-brand-600',
        'secondary' => 'bg-white text-brand-700 ring-1 ring-inset ring-brand-200 hover:bg-brand-50 focus-visible:outline-brand-600 dark:bg-transparent dark:text-white dark:ring-white/20 dark:hover:bg-white/5',
        'accent' => 'bg-accent-500 text-white shadow-sm hover:bg-accent-600 focus-visible:outline-accent-500',
        'ghost' => 'text-gray-700 hover:bg-gray-100 focus-visible:outline-brand-600 dark:text-white/80 dark:hover:bg-white/10',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2.5 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];
@endphp

<{{ $tag }}
    {{ $attributes->class([
        'inline-flex items-center justify-center gap-2 rounded-md font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-50 disabled:pointer-events-none',
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
    ]) }}
>
    {{ $slot }}
</{{ $tag }}>
