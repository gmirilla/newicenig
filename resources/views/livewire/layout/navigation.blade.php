<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

@php
    $membership = auth()->user()->currentMembership();

    $links = [
        ['label' => 'Dashboard', 'route' => 'member.dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['label' => 'Renew Membership', 'route' => 'member.renew', 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
        ['label' => 'Profile', 'route' => 'member.profile', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
    ];
@endphp

<div x-data="{ mobileOpen: false }">
    <!-- Mobile top bar -->
    <div class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 lg:hidden dark:border-white/10 dark:bg-slate-900">
        <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
            <img src="{{ asset('images/icen-mark.jpg') }}" alt="ICEN" class="h-8 w-8 rounded-full object-cover">
            <span class="font-bold text-slate-900 dark:text-white">ICEN</span>
        </a>
        <button @click="mobileOpen = ! mobileOpen" class="rounded-md p-2 text-slate-500 dark:text-slate-400" aria-label="Toggle menu">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <!-- Sidebar -->
    <aside
        x-cloak
        :class="mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col border-r border-slate-200 bg-white transition-transform duration-200 lg:translate-x-0 dark:border-white/10 dark:bg-slate-900"
    >
        <div class="hidden items-center gap-2 border-b border-slate-200 px-6 py-5 lg:flex dark:border-white/10">
            <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
                <img src="{{ asset('images/icen-mark.jpg') }}" alt="ICEN" class="h-9 w-9 rounded-full object-cover">
                <span class="text-lg font-bold text-slate-900 dark:text-white">ICEN</span>
            </a>
        </div>

        <nav class="flex-1 space-y-1 px-3 py-6">
            @foreach ($links as $link)
                <a
                    href="{{ route($link['route']) }}"
                    wire:navigate
                    @click="mobileOpen = false"
                    @class([
                        'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition',
                        'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-400' => request()->routeIs($link['route']),
                        'text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5' => ! request()->routeIs($link['route']),
                    ])
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}" />
                    </svg>
                    {{ $link['label'] }}
                </a>
            @endforeach

            @if ($membership?->isActive())
                <a
                    href="{{ route('member.certificate') }}"
                    target="_blank"
                    class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Certificate
                </a>
            @endif

            @if (auth()->user()->hasAnyRole(\App\Models\User::PANEL_ROLES))
                <a
                    href="{{ url('/admin') }}"
                    class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Admin Dashboard
                </a>
            @endif
        </nav>

        <div class="border-t border-slate-200 p-3 dark:border-white/10">
            <div class="flex items-center gap-3 rounded-md px-3 py-2">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                    {{ Str::of(auth()->user()->name)->substr(0, 1)->upper() }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <button wire:click="logout" class="mt-1 flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Log out
            </button>
        </div>
    </aside>

    <!-- Mobile overlay -->
    <div x-show="mobileOpen" x-cloak @click="mobileOpen = false" class="fixed inset-0 z-20 bg-slate-900/50 lg:hidden"></div>
</div>
