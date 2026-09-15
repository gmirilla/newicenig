<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800 dark:text-slate-200">
            {{ __('My Documents') }}
        </h2>
    </x-slot>

    <div class="mx-auto max-w-3xl">
        <livewire:portal.my-documents />
    </div>
</x-app-layout>
