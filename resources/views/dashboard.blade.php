<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800 dark:text-slate-200">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        $membership = auth()->user()->currentMembership();
        $isRevoked = $membership?->status === \App\Enums\MembershipStatus::Revoked;
        $latestNotices = $isRevoked
            ? collect()
            : \App\Models\Notice::visible()
                ->with('membershipTiers')
                ->orderByDesc('is_pinned')
                ->orderByDesc('published_at')
                ->get()
                ->filter(fn ($notice) => $notice->isVisibleToTier($membership?->membership_tier_id))
                ->take(3);
        $latestDocuments = $isRevoked ? collect() : auth()->user()->documents()->latest()->take(3)->get();
    @endphp

    <div class="space-y-8">
        <x-card>
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Welcome back,</p>
                    <h3 class="text-2xl font-bold text-slate-900 dark:text-white">{{ auth()->user()->name }}</h3>
                </div>

                @if ($membership)
                    <x-badge :variant="$membership->status->color()">{{ $membership->status->label() }}</x-badge>
                @else
                    <x-badge variant="gray">No membership yet</x-badge>
                @endif
            </div>
        </x-card>

        @if ($latestNotices->isNotEmpty())
            <x-card>
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Notice board</h3>
                    <a href="{{ route('member.notices') }}" wire:navigate class="text-sm font-medium text-brand-600 hover:underline dark:text-brand-400">View all</a>
                </div>
                <div class="mt-4 divide-y divide-slate-200 dark:divide-white/10">
                    @foreach ($latestNotices as $notice)
                        <div class="py-3">
                            <div class="flex items-center gap-2">
                                @if ($notice->is_pinned)
                                    <x-badge variant="brand">Pinned</x-badge>
                                @endif
                                <p class="font-medium text-slate-900 dark:text-white">{{ $notice->title }}</p>
                            </div>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $notice->published_at?->format('M j, Y') }}</p>
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endif

        @if ($latestDocuments->isNotEmpty())
            <x-card>
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">My documents</h3>
                    <a href="{{ route('member.documents') }}" wire:navigate class="text-sm font-medium text-brand-600 hover:underline dark:text-brand-400">View all</a>
                </div>
                <div class="mt-4 divide-y divide-slate-200 dark:divide-white/10">
                    @foreach ($latestDocuments as $document)
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <x-badge variant="brand">{{ ucfirst($document->type) }}</x-badge>
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $document->title }}</p>
                                </div>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Added {{ $document->created_at->format('M j, Y') }}</p>
                            </div>
                            <a href="{{ route('member.documents.download', $document) }}" target="_blank" class="text-sm font-medium text-brand-600 hover:underline dark:text-brand-400">Download</a>
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endif

        <div class="grid gap-6 sm:grid-cols-3">
            <x-card>
                <x-stat-tile :value="$membership?->membershipTier->name ?? '—'" label="Membership tier" />
            </x-card>
            <x-card>
                <x-stat-tile :value="$membership?->membership_number ?? '—'" label="Membership number" />
            </x-card>
            <x-card>
                <x-stat-tile :value="$membership?->expires_at?->format('M j, Y') ?? '—'" label="Expires" />
            </x-card>
        </div>

        @if ($membership?->isActive())
        <!-- disable rendering oof automated certificate download for now in case client need it implemented later
            <x-card>
                <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Your certificate</h3>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Download your ICEN membership certificate as a PDF.</p>
                    </div>
                    <x-button href="{{ route('member.certificate') }}" target="_blank">Download certificate</x-button>
                </div>
            </x-card>
        -->

            <x-card>
                <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Change your membership level</h3>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Apply to upgrade to a higher membership tier.</p>
                    </div>
                    <x-button href="{{ route('member.change-level') }}">Change level</x-button>
                </div>
            </x-card>
        @elseif ($membership && $membership->status->value === 'expired')
            <x-card>
                <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Your membership has expired</h3>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Renew now to regain full member access and benefits.</p>
                    </div>
                    <x-button href="{{ route('member.renew') }}">Renew membership</x-button>
                </div>
            </x-card>
        @elseif ($isRevoked)
            <x-card>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Your membership has been revoked</h3>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    Your ICEN membership has been revoked, and access to the notice board and member documents has been restricted. If you believe this is in error, please contact ICEN support.
                </p>
            </x-card>
        @elseif (! $membership)
            <x-card>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Get started</h3>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    You don't have a membership application on file yet.
                </p>
                <x-button href="{{ route('join') }}" class="mt-4">Apply for membership</x-button>
            </x-card>
        @else
            <x-card>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Application under review</h3>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    Your membership application is being reviewed by our registration committee. We'll notify you by email once it's approved.
                </p>
            </x-card>
        @endif

        <x-card>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Payment history</h3>
            @php $payments = auth()->user()->payments()->latest()->get(); @endphp
            @if ($payments->isEmpty())
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No payments on file yet.</p>
            @else
                <div class="mt-4 divide-y divide-slate-200 dark:divide-white/10">
                    @foreach ($payments as $payment)
                        <div class="flex items-center justify-between py-3 text-sm">
                            <div>
                                <p class="font-medium text-slate-900 dark:text-white">{{ $payment->reference }}</p>
                                <p class="text-slate-500 dark:text-slate-400">{{ $payment->created_at->format('M j, Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium text-slate-900 dark:text-white">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</p>
                                <x-badge :variant="$payment->status->color()">{{ $payment->status->label() }}</x-badge>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>
</x-app-layout>
