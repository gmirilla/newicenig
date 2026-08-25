<x-layouts.marketing title="News">
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <x-section-header
            align="center"
            eyebrow="News & Publications"
            heading="What's happening at ICEN"
        />

        @if ($posts->isEmpty())
            <p class="mt-14 text-center text-sm text-slate-500 dark:text-slate-400">
                No articles published yet. Add them from the admin panel under Content → Posts.
            </p>
        @else
            <div class="mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <a href="{{ route('news.show', $post) }}" wire:navigate>
                        <x-card class="h-full">
                            @if ($post->coverUrl())
                                <img src="{{ $post->coverUrl() }}" alt="{{ $post->title }}" class="-m-6 mb-4 h-40 w-[calc(100%+3rem)] rounded-t-xl object-cover">
                            @endif
                            <x-badge variant="brand">{{ ucfirst($post->category) }}</x-badge>
                            <h3 class="mt-3 text-lg font-semibold text-slate-900 dark:text-white">{{ $post->title }}</h3>
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $post->excerpt }}</p>
                            <p class="mt-4 text-xs text-slate-400">{{ $post->published_at?->format('M j, Y') }}</p>
                        </x-card>
                    </a>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $posts->links() }}
            </div>
        @endif
    </section>
</x-layouts.marketing>
