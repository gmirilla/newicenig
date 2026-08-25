<x-layouts.guest>
    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Set your password</h2>
    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
        Welcome, {{ $user->name }}. Choose a password to finish setting up your ICEN member account.
    </p>

    <livewire:auth.set-password-form :user="$user" />
</x-layouts.guest>
