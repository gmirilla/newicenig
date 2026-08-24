<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Renew Membership') }}
        </h2>
    </x-slot>

    <div class="mx-auto max-w-xl">
        <livewire:portal.renewal-flow />
    </div>
</x-app-layout>
