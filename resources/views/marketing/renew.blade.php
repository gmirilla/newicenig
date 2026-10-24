@php
    $renewHeading = 'Renew your '.app(\App\Settings\BrandingSettings::class)->brand_short_name.' membership';
@endphp

<x-layouts.marketing title="Renew Your Membership">
    <section class="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
        <x-section-header
            align="center"
            eyebrow="Membership renewal"
            :heading="$renewHeading"
        />

        <div class="mx-auto mt-12 max-w-xl">
            <livewire:portal.renewal-flow />
        </div>
    </section>
</x-layouts.marketing>
