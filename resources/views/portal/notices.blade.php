<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800 dark:text-slate-200">
            {{ __('Notice Board') }}
        </h2>
    </x-slot>

    <div class="mx-auto max-w-3xl">
        <livewire:portal.notice-board />
    </div>
</x-app-layout>
