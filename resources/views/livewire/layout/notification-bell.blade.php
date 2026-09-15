<div x-data="{ open: false }" class="relative" wire:poll.60s>
    <button
        @click="open = ! open"
        @click.outside="open = false"
        type="button"
        class="relative rounded-md p-2 text-slate-500 hover:bg-slate-50 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-slate-200"
        aria-label="Notifications"
    >
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if ($this->unreadCount > 0)
            <span class="absolute -right-0.5 -top-0.5 flex h-4.5 min-w-[1.125rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white">
                {{ $this->unreadCount > 9 ? '9+' : $this->unreadCount }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition
        class="absolute right-0 z-40 mt-2 w-80 rounded-lg border border-slate-200 bg-white shadow-lg dark:border-white/10 dark:bg-slate-900"
    >
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-white/10">
            <span class="text-sm font-semibold text-slate-900 dark:text-white">Notifications</span>
            @if ($this->unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs font-medium text-brand-600 hover:underline dark:text-brand-400">Mark all read</button>
            @endif
        </div>

        <div class="max-h-96 divide-y divide-slate-100 overflow-y-auto dark:divide-white/5">
            @forelse ($this->recent as $notification)
                <a
                    href="{{ $notification->data['url'] ?? '#' }}"
                    wire:click="markAsRead('{{ $notification->id }}')"
                    wire:navigate
                    class="block px-4 py-3 text-sm hover:bg-slate-50 dark:hover:bg-white/5"
                >
                    <div class="flex items-start gap-2">
                        @if (! $notification->read_at)
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-brand-500"></span>
                        @else
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-transparent"></span>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-slate-900 dark:text-white">{{ $notification->data['title'] ?? 'Notification' }}</p>
                            @if (! empty($notification->data['body']))
                                <p class="mt-0.5 truncate text-slate-500 dark:text-slate-400">{{ $notification->data['body'] }}</p>
                            @endif
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </a>
            @empty
                <p class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">No notifications yet.</p>
            @endforelse
        </div>

        <div class="border-t border-slate-200 px-4 py-2 dark:border-white/10">
            <a href="{{ route('member.notices') }}" wire:navigate class="text-xs font-medium text-brand-600 hover:underline dark:text-brand-400">View notice board</a>
        </div>
    </div>
</div>
