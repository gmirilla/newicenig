<x-layouts.marketing title="Diaspora Membership">
    <section class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
        <x-section-header
            align="center"
            eyebrow="For members abroad"
            heading="ICEN Diaspora Membership"
            subheading="ICEN welcomes chartered economists living outside Nigeria. If you're applying from abroad, you can pay your membership fee by bank transfer to one of our official ICEN Diaspora accounts below — no Nigerian bank account required."
        />
    </section>

    <section class="mx-auto max-w-4xl px-4 pb-8 sm:px-6 lg:px-8">
        <x-card>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">How it works</h3>
            <ol class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-400">
                <li class="flex gap-3">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">1</span>
                    <span>Transfer your membership application fee to one of the official ICEN Diaspora bank accounts listed below.</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">2</span>
                    <span>Start your application, select your country of residence, and choose <strong>Bank transfer</strong> as your payment method and select the Bank account paid to.</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">3</span>
                    <span>Upload proof of your transfer (a receipt or screenshot) and submit your application.</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">4</span>
                    <span>Our team verifies your payment and reviews your application — you'll be notified by email once it's confirmed.</span>
                </li>
            </ol>
        </x-card>
    </section>

    <section class="mx-auto max-w-4xl px-4 pb-16 sm:px-6 lg:px-8">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Membership Application Fees</h3>

        @if ($tiers->isEmpty())
            <x-card class="mt-4">
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Membership tiers are being updated. Please <a href="{{ route('contact') }}" class="font-medium text-brand-600 hover:underline dark:text-brand-400">contact us</a> to enquire about fees.
                </p>
            </x-card>
        @else
            <div class="mt-4 grid gap-6 sm:grid-cols-2">
                @foreach ($tiers as $tier)
                    <x-card>
                        <h4 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $tier->name }}</h4>
                        @if ($tier->abbreviation)
                            <p class="text-sm text-brand-600 dark:text-brand-400"><b>{{ $tier->abbreviation }}</p>
                        @endif
                        <dl class="mt-4 space-y-2 text-sm">
                            @foreach ($tier->currenciesWithRegistrationFee() as $currency)
                                <div class="flex justify-between gap-4">
                                    <dt class="text-slate-500 dark:text-slate-400">{{ $currency }}</dt>
                                    <dd class="font-medium text-slate-900 dark:text-white">{{ $currency }} {{ number_format($tier->registrationFeeFor($currency), 2) }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </x-card>
                @endforeach
            </div>
        @endif
    </section>

    <section class="mx-auto max-w-4xl px-4 pb-16 sm:px-6 lg:px-8">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Official ICEN Diaspora accounts</h3>

        @if ($bankAccounts->isEmpty())
            <x-card class="mt-4">
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Bank account details are being updated. Please <a href="{{ route('contact') }}" class="font-medium text-brand-600 hover:underline dark:text-brand-400">contact us</a> for payment instructions in the meantime.
                </p>
            </x-card>
        @else
            <div class="mt-4 grid gap-6 sm:grid-cols-2">
                @foreach ($bankAccounts as $account)
                    <x-card>
                        <p class="text-sm font-semibold uppercase tracking-wide text-brand-600 dark:text-brand-400">{{ $account->currency }}</p>
                        <h4 class="mt-1 text-lg font-semibold text-slate-900 dark:text-white">{{ $account->bank_name }}</h4>
                        <dl class="mt-4 space-y-2 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Account name</dt>
                                <dd class="text-right font-medium text-slate-900 dark:text-white">{{ $account->account_name }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Account number</dt>
                                <dd class="text-right font-medium text-slate-900 dark:text-white">{{ $account->account_number }}</dd>
                            </div>
                        </dl>
                        @if ($account->instructions)
                            <p class="mt-4 rounded-md bg-slate-50 p-3 text-xs text-slate-600 dark:bg-white/5 dark:text-slate-400">{{ $account->instructions }}</p>
                        @endif
                    </x-card>
                @endforeach
            </div>
        @endif
    </section>

    <section class="mx-auto max-w-4xl px-4 pb-20 text-center sm:px-6 lg:px-8">
        <x-card>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Ready to apply?</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                Begin your application and select bank transfer at checkout once you've made your payment.
            </p>
            <div class="mt-6">
                <x-button href="{{ route('join') }}" size="lg">Begin your application</x-button>
            </div>
        </x-card>
    </section>
</x-layouts.marketing>
