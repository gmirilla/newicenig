<x-layouts.marketing title="Join ICEN">
    <section class="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
        <x-section-header
            align="center"
            eyebrow="Membership application"
            heading="Join the Institute"
        />

        <div class="mt-12">
            <livewire:portal.membership-registration-wizard
                :tier="isset($tier) ? \App\Models\MembershipTier::where('slug', $tier)->where('is_active', true)->first() : null"
            />
        </div>
    </section>
</x-layouts.marketing>
