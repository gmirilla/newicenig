<x-layouts.marketing title="Payment successful">
    <section class="mx-auto max-w-2xl px-4 py-24 text-center sm:px-6 lg:px-8">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-500/15">
            <svg class="h-8 w-8 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="mt-6 text-2xl font-bold text-slate-900 dark:text-white">Payment received</h1>
        <p class="mt-3 text-slate-600 dark:text-slate-400">
            @if ($payment->status->value === 'successful')
                Thank you — your payment of {{ $payment->currency }} {{ number_format($payment->amount, 2) }} was successful.
                @if (! $payment->user_id)
                    Check your email for a link to set your password and access your member dashboard.
                @else
                    You can now view the details from your member dashboard.
                @endif
            @else
                We're still confirming your payment with Paystack. This can take a few moments — refresh this page shortly,
                or check your email for confirmation.
            @endif
        </p>

        <div class="mt-8 flex justify-center gap-4">
            <x-button href="{{ route('home') }}" variant="secondary">Back to home</x-button>
            @auth
                <x-button href="{{ route('member.dashboard') }}">Go to dashboard</x-button>
            @endauth
        </div>
    </section>
</x-layouts.marketing>
