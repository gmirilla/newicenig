<x-layouts.marketing title="Resources">
    <section class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
        <x-section-header
            align="center"
            eyebrow="Resources"
            heading="Downloads"
            subheading="Forms, guidelines, and byelaws for members and prospective applicants."
        />

        @if ($downloads->isEmpty())
            <p class="mt-14 text-center text-sm text-gray-500 dark:text-gray-400">
                No downloads published yet. Add them from the admin panel under Content → Downloads.
            </p>
        @else
            <div class="mt-14 space-y-10">
                @foreach ($downloads as $category => $items)
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $category }}</h2>
                        <div class="mt-4 divide-y divide-gray-200 rounded-xl border border-gray-200 dark:divide-white/10 dark:border-white/10">
                            @foreach ($items as $download)
                                <a href="{{ $download->fileUrl() ?? '#' }}" target="_blank" class="flex items-center justify-between px-5 py-4 hover:bg-gray-50 dark:hover:bg-white/5">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $download->title }}</p>
                                        @if ($download->description)
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $download->description }}</p>
                                        @endif
                                    </div>
                                    <svg class="h-5 w-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.marketing>
