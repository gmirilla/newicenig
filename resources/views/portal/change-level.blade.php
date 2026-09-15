<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800 dark:text-slate-200">
            {{ __('Change Membership Level') }}
        </h2>
    </x-slot>

    <div class="mx-auto max-w-3xl">
        <livewire:portal.change-level-flow />
    </div>
</x-app-layout>
