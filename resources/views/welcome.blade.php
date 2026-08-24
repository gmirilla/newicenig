<x-layouts.marketing>
    <!-- Hero -->
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-50 to-white dark:from-brand-950/40 dark:to-gray-950"></div>

        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <p class="inline-flex items-center rounded-full bg-brand-100 px-3 py-1 text-sm font-medium text-brand-800 dark:bg-brand-500/15 dark:text-brand-400">
                    Chartered. Certified. Trusted.
                </p>

                <h1 class="mt-6 text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl dark:text-white">
                    The professional home for Nigeria's economists
                </h1>

                <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-400">
                    ICEN certifies, develops, and represents professional economists across Nigeria — setting the
                    standard for economic practice through membership, continuing education, and advocacy.
                </p>

                <div class="mt-10 flex items-center justify-center gap-4">
                    <x-button href="{{ Route::has('join') ? route('join') : '#' }}" size="lg">Become a member</x-button>
                    <x-button href="{{ Route::has('about') ? route('about') : '#' }}" variant="secondary" size="lg">Learn more</x-button>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="border-y border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-gray-900/50">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <dl class="grid grid-cols-2 gap-8 sm:grid-cols-4">
                <x-stat-tile value="3,200+" label="Certified members" />
                <x-stat-tile value="24" label="Years of standards" />
                <x-stat-tile value="36" label="States represented" />
                <x-stat-tile value="120+" label="CPD hours run yearly" />
            </dl>
        </div>
    </section>

    <!-- Why join -->
    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <x-section-header
            align="center"
            eyebrow="Why ICEN"
            heading="Membership that means something"
            subheading="ICEN membership is a recognised mark of professional competence — backed by certification standards, continuing development, and a national network of peers."
        />

        <div class="mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Professional certification</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Earn a chartered designation recognised by employers, regulators, and institutions across Nigeria.
                </p>
            </x-card>
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Continuing education</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Access CPD-accredited events, workshops, and resources to keep your practice current.
                </p>
            </x-card>
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">National network</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Connect with economists across sectors and states through chapters, events, and the member directory.
                </p>
            </x-card>
        </div>
    </section>

    <!-- CTA -->
    <section class="bg-brand-700">
        <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold tracking-tight text-white">Ready to join?</h2>
            <p class="mt-4 text-brand-100">
                Applications are reviewed by our registration committee — most decisions are made within two weeks.
            </p>
            <div class="mt-8">
                <x-button href="{{ Route::has('join') ? route('join') : '#' }}" variant="accent" size="lg">Start your application</x-button>
            </div>
        </div>
    </section>
</x-layouts.marketing>
