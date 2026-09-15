@props(['disabled' => false])

<input
    type="file"
    @disabled($disabled)
    {{ $attributes->merge(['class' =>
        'mt-1 block w-full text-sm text-slate-600 dark:text-slate-400 '.
        'file:mr-4 file:rounded-md file:border-0 file:bg-brand-50 file:px-4 file:py-2 '.
        'file:text-sm file:font-semibold file:text-brand-700 file:transition file:cursor-pointer '.
        'hover:file:bg-brand-100 dark:file:bg-brand-500/15 dark:file:text-brand-400 dark:hover:file:bg-brand-500/25 '.
        'focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2'
    ]) }}
>
