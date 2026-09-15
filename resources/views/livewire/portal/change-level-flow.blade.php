<div>
    @if (! $this->membership || ! $this->membership->isActive())
        <x-card>
            <p class="text-sm text-slate-600 dark:text-slate-400">You need an active membership before you can apply to change your level.</p>
        </x-card>
    @else
        <x-card>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Change your membership level</h3>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                You're currently on the <strong>{{ $this->membership->membershipTier->name }}</strong> tier. Choose a higher tier below to apply for an upgrade.
            </p>
        </x-card>

        @if ($this->availableTiers->isEmpty())
            <x-card class="mt-6">
                <p class="text-sm text-slate-600 dark:text-slate-400">There are no higher membership tiers available to upgrade to right now.</p>
            </x-card>
        @else
            <div class="mt-6 grid gap-6 sm:grid-cols-2">
                @foreach ($this->availableTiers as $tier)
                    <x-card class="flex h-full flex-col justify-between {{ $selectedTierId === $tier->id ? 'ring-2 ring-brand-500' : '' }}">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $tier->name }}</h3>
                            @if ($tier->abbreviation)
                                <p class="text-sm text-brand-600 dark:text-brand-400">{{ $tier->abbreviation }}</p>
                            @endif
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $tier->description }}</p>
                            <p class="mt-4 text-2xl font-bold text-slate-900 dark:text-white">
                                {{ $tier->currency }} {{ number_format($tier->registration_fee, 0) }}
                            </p>
                        </div>
                        <x-button
                            wire:click="selectTier({{ $tier->id }})"
                            variant="{{ $selectedTierId === $tier->id ? 'primary' : 'secondary' }}"
                            class="mt-6 w-full justify-center"
                        >
                            {{ $selectedTierId === $tier->id ? 'Selected' : 'Select '.$tier->name }}
                        </x-button>
                    </x-card>
                @endforeach
            </div>

            @if ($this->selectedTier)
                <x-card class="mt-6">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Complete your application</h3>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        Upgrading to <strong>{{ $this->selectedTier->name }}</strong> — {{ $this->selectedTier->currency }} {{ number_format($this->selectedTier->registration_fee, 2) }}
                    </p>

                    <x-payment-method-fields
                        :payment-method="$paymentMethod"
                        :bank-account-id="$bankAccountId"
                        :bank-accounts="$this->bankAccounts"
                    />

                    <x-button wire:click="submit" wire:loading.attr="disabled" wire:target="submit,proofOfPayment" class="mt-8 w-full justify-center">
                        <span wire:loading.remove wire:target="submit">{{ $paymentMethod === 'bank_transfer' ? 'Submit application' : 'Apply & pay' }}</span>
                        <span wire:loading wire:target="submit">{{ $paymentMethod === 'bank_transfer' ? 'Submitting…' : 'Redirecting to Paystack…' }}</span>
                    </x-button>
                </x-card>
            @endif
        @endif
    @endif
</div>
