<div>
    @if (! $this->membership)
        <x-card>
            <p class="text-sm text-slate-600 dark:text-slate-400">You don't have a membership to renew yet.</p>
            <x-button href="{{ route('join') }}" class="mt-4">Apply for membership</x-button>
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
                    <dt class="font-semibold text-slate-900 dark:text-white">Renewal fee</dt>
                    <dd class="font-semibold text-slate-900 dark:text-white">
                        {{ $this->membership->membershipTier->currency }} {{ number_format($this->membership->membershipTier->renewal_fee, 2) }}
                    </dd>
                </div>
            </dl>

            <x-button wire:click="renew" wire:loading.attr="disabled" class="mt-8 w-full justify-center">
                <span wire:loading.remove wire:target="renew">Pay & renew</span>
                <span wire:loading wire:target="renew">Redirecting to Paystack…</span>
            </x-button>
        </x-card>
    @endif
</div>
