@props([
    'value',
    'label',
])

<div {{ $attributes->class('flex flex-col') }}>
    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</dt>
    <dd class="mt-1 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $value }}</dd>
</div>
