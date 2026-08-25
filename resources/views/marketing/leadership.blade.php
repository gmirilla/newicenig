<x-layouts.marketing title="Leadership">
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <x-section-header
            align="center"
            eyebrow="Governing Council"
            heading="Meet the leadership"
            subheading="ICEN is governed by a council of senior economists elected to represent the profession and steward the Institute's standards."
        />

        @if ($teamMembers->isEmpty())
            <p class="mt-14 text-center text-sm text-slate-500 dark:text-slate-400">
                No council members published yet. Add them from the admin panel under Content → Team Members.
            </p>
        @else
            <div class="mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($teamMembers as $member)
                    <x-card>
                        <div class="flex items-center gap-4">
                            @if ($member->photoUrl())
                                <img src="{{ $member->photoUrl() }}" alt="{{ $member->name }}" class="h-16 w-16 rounded-full object-cover">
                            @else
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-100 text-lg font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                                    {{ Str::of($member->name)->substr(0, 1)->upper() }}
                                </div>
                            @endif
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $member->name }}</h3>
                                <p class="text-sm text-brand-600 dark:text-brand-400">{{ $member->role_title }}</p>
                            </div>
                        </div>
                        @if ($member->bio)
                            <div class="prose prose-sm prose-gray mt-4 max-w-none dark:prose-invert">
                                {!! $member->bio !!}
                            </div>
                        @endif
                    </x-card>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.marketing>
