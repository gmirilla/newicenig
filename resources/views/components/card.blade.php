@props(['padded' => true])

<div {{ $attributes->class([
    'rounded-xl bg-white shadow-card ring-1 ring-slate-900/5 transition hover:shadow-card-hover dark:bg-slate-800 dark:ring-white/10',
    'p-6' => $padded,
]) }}>
    {{ $slot }}
</div>
