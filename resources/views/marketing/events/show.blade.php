<x-layouts.marketing :title="$event->title">
    <article class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <a href="{{ route('events.index') }}" wire:navigate class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
            &larr; Back to events
        </a>

        <h1 class="mt-6 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white">{{ $event->title }}</h1>

        @if ($event->coverUrl())
            <img src="{{ $event->coverUrl() }}" alt="{{ $event->title }}" class="mt-8 w-full rounded-xl object-cover">
        @endif

        <div class="mt-8 grid gap-4 sm:grid-cols-3">
            <x-card :padded="true">
                <x-stat-tile value="{{ $event->starts_at->format('M j, Y') }}" label="Date" />
            </x-card>
            <x-card :padded="true">
                <x-stat-tile value="{{ $event->is_virtual ? 'Virtual' : ($event->location ?: 'TBA') }}" label="Location" />
            </x-card>
            <x-card :padded="true">
                <x-stat-tile value="{{ $event->cpd_points ?? '—' }}" label="CPD points" />
            </x-card>
        </div>

        <div class="prose prose-gray mt-8 max-w-none dark:prose-invert">
            {!! $event->description !!}
        </div>

        @if ($event->registration_url)
            <div class="mt-8">
                <x-button href="{{ $event->registration_url }}" size="lg">Register for this event</x-button>
            </div>
        @endif
    </article>
</x-layouts.marketing>
