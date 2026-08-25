<x-layouts.marketing>
    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white">
        <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand-600/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -left-16 bottom-0 h-72 w-72 rounded-full bg-brand-950/40 blur-2xl"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <p class="inline-flex items-center gap-2 rounded-full border border-accent-400/40 bg-accent-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-accent-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent-400"></span>
                    Chartered. Certified. Trusted.
                </p>

                <h1 class="mt-6 text-4xl font-extrabold tracking-tight sm:text-6xl">
                    The professional home for Nigeria's economists
                </h1>

                <p class="mt-6 text-lg leading-8 text-brand-100">
                    ICEN certifies, develops, and represents professional economists across Nigeria — setting the
                    standard for economic practice through membership, continuing education, and advocacy.
                </p>

                <div class="mt-10 flex items-center justify-center gap-4">
                    <x-button href="{{ Route::has('join') ? route('join') : '#' }}" variant="accent" size="lg">Become a member</x-button>
                    <x-button href="{{ Route::has('about') ? route('about') : '#' }}" variant="secondary" size="lg" class="!bg-white/10 !text-white !ring-white/30 hover:!bg-white/20">Learn more</x-button>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="border-b border-brand-100 bg-brand-50">
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
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Professional certification</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    Earn a chartered designation recognised by employers, regulators, and institutions across Nigeria.
                </p>
            </x-card>
            <x-card>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Continuing education</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    Access CPD-accredited events, workshops, and resources to keep your practice current.
                </p>
            </x-card>
            <x-card>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">National network</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    Connect with economists across sectors and states through chapters, events, and the member directory.
                </p>
            </x-card>
        </div>
    </section>

    <!-- CTA -->
    <section class="bg-brand-surface">
        <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold tracking-tight text-white">Ready to join?</h2>
            <p class="mt-4 text-accent-200">
                Applications are reviewed by our registration committee — most decisions are made within two weeks.
            </p>
            <div class="mt-8">
                <x-button href="{{ Route::has('join') ? route('join') : '#' }}" variant="accent" size="lg">Start your application</x-button>
            </div>
        </div>
    </section>
</x-layouts.marketing>
