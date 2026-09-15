<div class="space-y-6">
    @forelse ($this->notices as $notice)
        <x-card>
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        @if ($notice->is_pinned)
                            <x-badge variant="brand">Pinned</x-badge>
                        @endif
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $notice->title }}</h3>
                    </div>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        {{ $notice->published_at?->format('M j, Y') }}
                    </p>
                </div>
            </div>

            <div class="prose prose-gray mt-4 max-w-none dark:prose-invert">
                {!! $notice->body !!}
            </div>
        </x-card>
    @empty
        <x-card>
            <p class="text-sm text-slate-600 dark:text-slate-400">There are no notices right now. Check back later.</p>
        </x-card>
    @endforelse
</div>
