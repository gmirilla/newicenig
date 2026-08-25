@php
    $links = [
        ['label' => 'About', 'route' => 'about'],
        ['label' => 'Leadership', 'route' => 'leadership'],
        ['label' => 'Membership', 'route' => 'membership'],
        ['label' => 'News', 'route' => 'news.index'],
        ['label' => 'Events', 'route' => 'events.index'],
        ['label' => 'Resources', 'route' => 'resources.index'],
        ['label' => 'Contact', 'route' => 'contact'],
    ];
@endphp

<header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-accent-100 bg-white/90 backdrop-blur dark:border-white/10 dark:bg-slate-900/90">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
            <img src="{{ asset('images/icen-mark.jpg') }}" alt="ICEN" class="h-10 w-10 rounded-full object-cover">
            <span class="text-lg font-bold tracking-tight text-accent-800 dark:text-accent-300">ICEN</span>
        </a>

        <div class="hidden lg:flex lg:items-center lg:gap-6">
            @foreach ($links as $link)
                @if (Route::has($link['route']))
                    <a
                        href="{{ route($link['route']) }}"
                        wire:navigate
                        class="text-sm font-medium text-slate-700 transition hover:text-accent-700 dark:text-slate-300 dark:hover:text-accent-400"
                    >
                        {{ $link['label'] }}
                    </a>
                @endif
            @endforeach
        </div>

        <div class="hidden lg:flex lg:items-center lg:gap-3">
            @auth
                <x-button href="{{ url('/member/dashboard') }}" variant="secondary" size="sm">Dashboard</x-button>
            @else
                <x-button href="{{ route('login') }}" variant="ghost" size="sm">Log in</x-button>
                <x-button href="{{ Route::has('join') ? route('join') : '#' }}" variant="primary" size="sm">Join ICEN</x-button>
            @endauth
        </div>

        <button @click="open = ! open" class="-me-2 inline-flex items-center justify-center rounded-md p-2 text-slate-500 lg:hidden dark:text-slate-400" aria-label="Toggle navigation">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{ hidden: open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{ hidden: !open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </nav>

    <div x-show="open" x-cloak class="border-t border-accent-100 lg:hidden dark:border-white/10">
        <div class="space-y-1 px-4 py-3">
            @foreach ($links as $link)
                @if (Route::has($link['route']))
                    <a href="{{ route($link['route']) }}" wire:navigate class="block rounded-md px-3 py-2 text-base font-medium text-slate-700 hover:bg-accent-50 hover:text-accent-800 dark:text-slate-300 dark:hover:bg-white/5">
                        {{ $link['label'] }}
                    </a>
                @endif
            @endforeach
            <div class="mt-3 flex flex-col gap-2 border-t border-accent-100 pt-3 dark:border-white/10">
                @auth
                    <x-button href="{{ url('/member/dashboard') }}" variant="secondary" size="sm">Dashboard</x-button>
                @else
                    <x-button href="{{ route('login') }}" variant="ghost" size="sm">Log in</x-button>
                    <x-button href="{{ Route::has('join') ? route('join') : '#' }}" variant="primary" size="sm">Join ICEN</x-button>
                @endauth
            </div>
        </div>
    </div>
</header>
