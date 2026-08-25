<x-layouts.marketing title="Events">
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <x-section-header
            align="center"
            eyebrow="Events & CPD"
            heading="Upcoming events"
            subheading="CPD-accredited events, workshops, and conferences for members and the wider profession."
        />

        @if ($upcoming->isEmpty())
            <p class="mt-14 text-center text-sm text-slate-500 dark:text-slate-400">
                No upcoming events published yet. Add them from the admin panel under Content → Events.
            </p>
        @else
            <div class="mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($upcoming as $event)
                    <a href="{{ route('events.show', $event) }}" wire:navigate>
                        <x-card class="h-full">
                            <p class="text-sm font-semibold text-brand-600 dark:text-brand-400">
                                {{ $event->starts_at->format('M j, Y \a\t g:ia') }}
                            </p>
                            <h3 class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">{{ $event->title }}</h3>
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                                {{ $event->is_virtual ? 'Virtual' : $event->location }}
                                @if ($event->cpd_points)
                                    &middot; {{ $event->cpd_points }} CPD points
                                @endif
                            </p>
                        </x-card>
                    </a>
                @endforeach
            </div>
        @endif

        @if ($past->isNotEmpty())
            <div class="mt-20">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Past events</h2>
                <div class="mt-6 divide-y divide-slate-200 dark:divide-white/10">
                    @foreach ($past as $event)
                        <a href="{{ route('events.show', $event) }}" wire:navigate class="flex items-center justify-between py-4">
                            <span class="text-sm font-medium text-slate-900 dark:text-white">{{ $event->title }}</span>
                            <span class="text-sm text-slate-500 dark:text-slate-400">{{ $event->starts_at->format('M j, Y') }}</span>
                        </a>
                    @endforeach
                </div>
                <div class="mt-6">
                    {{ $past->links() }}
                </div>
            </div>
        @endif
    </section>
</x-layouts.marketing>
