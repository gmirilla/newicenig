<x-layouts.marketing title="Membership">
    <section class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
        <x-section-header
            align="center"
            eyebrow="Membership"
            heading="Join the Institute"
            subheading="ICEN membership recognises professional competence in economics and gives you access to certification, continuing education, and a national network of peers."
        />
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
        @php $tiers = \App\Models\MembershipTier::where('is_active', true)->orderBy('sort_order')->get(); @endphp

        @if ($tiers->isEmpty())
            <x-card>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">No membership tiers are open right now</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    Add tiers from the admin panel under Membership → Tiers, or get in touch to enquire.
                </p>
                <div class="mt-6">
                    <x-button href="{{ route('contact') }}" variant="secondary">Contact us to enquire</x-button>
                </div>
            </x-card>
        @else
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($tiers as $tier)
                    <x-card class="flex h-full flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $tier->name }}</h3>
                            @if ($tier->abbreviation)
                                <p class="text-sm text-brand-600 dark:text-brand-400">{{ $tier->abbreviation }}</p>
                            @endif
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $tier->description }}</p>
                            <p class="mt-4 text-2xl font-bold text-slate-900 dark:text-white">
                                {{ $tier->currency }} {{ number_format($tier->registration_fee, 0) }}
                            </p>
                            @if (filled($tier->benefits))
                                <ul class="mt-4 space-y-2 text-sm text-slate-600 dark:text-slate-400">
                                    @foreach ($tier->benefits as $benefit)
                                        <li class="flex items-start gap-2">
                                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            {{ $benefit }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <x-button href="{{ route('join.tier', $tier->slug) }}" class="mt-6 w-full justify-center">
                            Apply for {{ $tier->name }}
                        </x-button>
                    </x-card>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.marketing>
