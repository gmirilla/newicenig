<x-layouts.marketing title="Contact">
    <section class="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
        <x-section-header
            align="center"
            eyebrow="Contact"
            heading="Get in touch"
        />

        @php $settings = app(\App\Settings\GeneralSettings::class); @endphp

        <div class="mt-14 grid gap-10 lg:grid-cols-5">
            <div class="lg:col-span-2 space-y-6">
                <x-card>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Address</h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $settings->org_address }}</p>
                </x-card>
                <x-card>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Phone</h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $settings->org_phone }}</p>
                </x-card>
                <x-card>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Email</h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $settings->org_email }}</p>
                </x-card>
            </div>

            <div class="lg:col-span-3">
                <x-card>
                    <livewire:marketing.contact-form />
                </x-card>
            </div>
        </div>
    </section>
</x-layouts.marketing>
