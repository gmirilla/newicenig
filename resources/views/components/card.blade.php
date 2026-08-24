@props(['padded' => true])

<div {{ $attributes->class([
    'rounded-xl bg-white shadow-card ring-1 ring-gray-900/5 transition hover:shadow-card-hover dark:bg-gray-800 dark:ring-white/10',
    'p-6' => $padded,
]) }}>
    {{ $slot }}
</div>
