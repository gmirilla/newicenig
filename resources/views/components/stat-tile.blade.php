@props([
    'value',
    'label',
])

<div {{ $attributes->class('flex flex-col') }}>
    <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</dt>
    <dd class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $value }}</dd>
</div>
