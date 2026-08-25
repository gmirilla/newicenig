<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800 dark:text-slate-200">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php $membership = auth()->user()->currentMembership(); @endphp

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
            <x-card>
                <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Your certificate</h3>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Download your ICEN membership certificate as a PDF.</p>
                    </div>
                    <x-button href="{{ route('member.certificate') }}" target="_blank">Download certificate</x-button>
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
