@props([
    'eyebrow' => null,
    'heading',
    'subheading' => null,
    'align' => 'left',
])

<div {{ $attributes->class(['max-w-2xl', 'mx-auto text-center' => $align === 'center']) }}>
    @if ($eyebrow)
        <p class="text-sm font-semibold uppercase tracking-wide text-brand-600 dark:text-brand-400">{{ $eyebrow }}</p>
    @endif

    <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
        {{ $heading }}
    </h2>

    @if ($subheading)
        <p class="mt-4 text-lg text-slate-600 dark:text-slate-400">
            {{ $subheading }}
        </p>
    @endif
</div>
