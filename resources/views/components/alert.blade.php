@props(['variant' => 'brand'])

@php
    $variants = [
        'brand' => 'bg-brand-50 text-brand-800 ring-1 ring-inset ring-brand-200 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-500/20',
        'success' => 'bg-brand-50 text-brand-800 ring-1 ring-inset ring-brand-200 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-500/20',
        'warning' => 'bg-amber-50 text-amber-800 ring-1 ring-inset ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20',
        'danger' => 'bg-red-50 text-red-800 ring-1 ring-inset ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/20',
    ];
@endphp

<div {{ $attributes->class([
    'rounded-md px-4 py-3 text-sm',
    $variants[$variant] ?? $variants['brand'],
]) }}>
    {{ $slot }}
</div>
