@props(['variant' => 'gray'])

@php
    $variants = [
        'gray' => 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-300',
        'brand' => 'bg-brand-100 text-brand-800 dark:bg-brand-500/15 dark:text-brand-400',
        'success' => 'bg-brand-100 text-brand-800 dark:bg-brand-500/15 dark:text-brand-400',
        'warning' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-400',
        'danger' => 'bg-accent-500/10 text-accent-600 dark:bg-accent-500/15 dark:text-accent-400',
    ];
@endphp

<span {{ $attributes->class([
    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
    $variants[$variant] ?? $variants['gray'],
]) }}>
    {{ $slot }}
</span>
