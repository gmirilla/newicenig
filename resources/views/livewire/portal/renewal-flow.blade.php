<div>
    @if (! $this->membership)
        @guest
            <x-card>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Find your membership</h3>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Enter your ICEN membership number and the email address on file to renew without logging in.</p>

                <form wire:submit="lookup" class="mt-6 space-y-4">
                    <div>
                        <x-input-label for="lookupMembershipNumber" value="Membership number" />
                        <x-text-input id="lookupMembershipNumber" type="text" class="mt-1 block w-full" wire:model="lookupMembershipNumber" required autofocus placeholder="ICEN/2024/00123" />
                        <x-input-error :messages="$errors->get('lookupMembershipNumber')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="lookupEmail" value="Email address" />
                        <x-text-input id="lookupEmail" type="email" class="mt-1 block w-full" wire:model="lookupEmail" required />
                        <x-input-error :messages="$errors->get('lookupEmail')" class="mt-2" />
                    </div>

                    @if ($lookupFailed)
                        <p class="text-sm text-red-600 dark:text-red-400">
                            We couldn't find a membership matching those details. Double-check your membership number and email, or <a href="{{ route('join') }}" class="underline">apply for a new membership</a>.
                        </p>
                    @endif

                    <x-button type="submit" wire:loading.attr="disabled" wire:target="lookup" class="w-full justify-center">
                        <span wire:loading.remove wire:target="lookup">Find my membership</span>
                        <span wire:loading wire:target="lookup">Searching…</span>
                    </x-button>
                </form>

                <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
                    Already know your password? <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:underline dark:text-brand-400">Log in</a> instead.
                </p>
            </x-card>
        @else
            <x-card>
                <p class="text-sm text-slate-600 dark:text-slate-400">You don't have a membership to renew yet.</p>
                <x-button href="{{ route('join') }}" class="mt-4">Apply for membership</x-button>
            </x-card>
        @endguest
    @elseif (! $this->isEligibleForRenewal)
        <x-card>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Renew your membership</h3>
            <p class="mt-4 text-sm text-slate-600 dark:text-slate-400">{{ $this->renewalIneligibleMessage }}</p>
        </x-card>
    @else
        <x-card>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Renew your membership</h3>

            <dl class="mt-6 divide-y divide-slate-200 text-sm dark:divide-white/10">
                <div class="flex justify-between py-3">
                    <dt class="text-slate-500 dark:text-slate-400">Current tier</dt>
                    <dd class="font-medium text-slate-900 dark:text-white">{{ $this->membership->membershipTier->name }}</dd>
                </div>
                <div class="flex justify-between py-3">
                    <dt class="text-slate-500 dark:text-slate-400">Current expiry</dt>
                    <dd class="font-medium text-slate-900 dark:text-white">{{ $this->membership->expires_at?->format('M j, Y') ?? '—' }}</dd>
                </div>
                <div class="flex justify-between py-3 text-base">
                    <dt class="font-semibold text-slate-900 dark:text-white">Annual dues</dt>
                    <dd class="font-semibold text-slate-900 dark:text-white">
                        {{ $this->displayCurrency }} {{ number_format($this->displayAmount ?? 0, 2) }}
                    </dd>
                </div>
            </dl>

            <div class="mt-6">
                <x-input-label for="yearOfInduction" value="Year of induction into ICEN" />
                <x-text-input id="yearOfInduction" type="number" min="1960" max="{{ now()->year }}" class="mt-1 block w-full" wire:model="yearOfInduction" required />
                <x-input-error :messages="$errors->get('yearOfInduction')" class="mt-2" />
            </div>

            @guest
                <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">
                    After payment, we'll email {{ $this->membership->email }} a link to set up a password so you can log in and manage your membership going forward.
                </p>
            @endguest

            <x-payment-method-fields
                :payment-method="$paymentMethod"
                :bank-account-id="$bankAccountId"
                :bank-accounts="$this->bankAccounts"
            />

            <x-button wire:click="renew" wire:loading.attr="disabled" wire:target="renew,proofOfPayment" class="mt-8 w-full justify-center">
                <span wire:loading.remove wire:target="renew">{{ $paymentMethod === 'bank_transfer' ? 'Submit renewal' : 'Pay & renew' }}</span>
                <span wire:loading wire:target="renew">{{ $paymentMethod === 'bank_transfer' ? 'Submitting…' : 'Redirecting to Paystack…' }}</span>
            </x-button>
        </x-card>
    @endif
</div>
