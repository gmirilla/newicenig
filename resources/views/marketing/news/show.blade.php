<x-layouts.marketing :title="$post->title">
    <article class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <a href="{{ route('news.index') }}" wire:navigate class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
            &larr; Back to news
        </a>

        <x-badge variant="brand" class="mt-6">{{ ucfirst($post->category) }}</x-badge>
        <h1 class="mt-3 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white">{{ $post->title }}</h1>
        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
            {{ $post->published_at?->format('F j, Y') }}
            @if ($post->author)
                &middot; {{ $post->author->name }}
            @endif
        </p>

        @if ($post->coverUrl())
            <img src="{{ $post->coverUrl() }}" alt="{{ $post->title }}" class="mt-8 w-full rounded-xl object-cover">
        @endif

        <div class="prose prose-gray mt-8 max-w-none dark:prose-invert">
            {!! $post->body !!}
        </div>
    </article>
</x-layouts.marketing>
