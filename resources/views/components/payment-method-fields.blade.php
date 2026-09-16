@props(['paymentMethod', 'bankAccountId', 'bankAccounts'])

<div class="mt-6 space-y-4">
    <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Payment method</h4>

    <div class="grid gap-3 {{ $bankAccounts->isNotEmpty() ? 'sm:grid-cols-2' : '' }}">
        <label class="flex cursor-pointer items-start gap-3 rounded-md border border-slate-200 p-4 dark:border-white/10">
            <input type="radio" wire:model.live="paymentMethod" value="paystack" class="mt-1 text-brand-600 focus:ring-brand-500">
            <span>
                <span class="block text-sm font-medium text-slate-900 dark:text-white">Pay online</span>
                <span class="block text-xs text-slate-500 dark:text-slate-400">Card, bank, or USSD via Paystack</span>
            </span>
        </label>
        @if ($bankAccounts->isNotEmpty())
            <label class="flex cursor-pointer items-start gap-3 rounded-md border border-slate-200 p-4 dark:border-white/10">
                <input type="radio" wire:model.live="paymentMethod" value="bank_transfer" class="mt-1 text-brand-600 focus:ring-brand-500">
                <span>
                    <span class="block text-sm font-medium text-slate-900 dark:text-white">Bank transfer</span>
                    <span class="block text-xs text-slate-500 dark:text-slate-400">Diaspora Payments (Transfer to Bank Account and upload proof of payment)</span>
                </span>
            </label>
        @endif
    </div>

    @if ($paymentMethod === 'bank_transfer' && $bankAccounts->isNotEmpty())
        <div class="rounded-md bg-slate-50 p-4 dark:bg-white/5">
            <x-input-label for="bankAccountId" value="Pay into" />
            <select id="bankAccountId" wire:model.live="bankAccountId" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                <option value="">Select a bank account…</option>
                @foreach ($bankAccounts as $account)
                    <option value="{{ $account->id }}">{{ $account->bank_name }} — {{ $account->account_name }} ({{ $account->account_number }}) — {{ $account->currency }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('bankAccountId')" class="mt-2" />

            @php $selectedAccount = $bankAccounts->firstWhere('id', (int) $bankAccountId); @endphp
            @if ($selectedAccount)
                <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                    <dt class="text-slate-500 dark:text-slate-400">Bank</dt>
                    <dd class="text-slate-900 dark:text-white">{{ $selectedAccount->bank_name }}</dd>
                    <dt class="text-slate-500 dark:text-slate-400">Account name</dt>
                    <dd class="text-slate-900 dark:text-white">{{ $selectedAccount->account_name }}</dd>
                    <dt class="text-slate-500 dark:text-slate-400">Account number</dt>
                    <dd class="text-slate-900 dark:text-white">{{ $selectedAccount->account_number }}</dd>
                    <dt class="text-slate-500 dark:text-slate-400">Currency</dt>
                    <dd class="text-slate-900 dark:text-white">{{ $selectedAccount->currency }}</dd>
                    @if ($selectedAccount->instructions)
                        <dt class="text-slate-500 dark:text-slate-400">Notes</dt>
                        <dd class="text-slate-900 dark:text-white">{{ $selectedAccount->instructions }}</dd>
                    @endif
                </dl>
            @endif

            <div class="mt-4">
                <x-input-label for="proofOfPayment" value="Proof of payment" />
                <x-file-input id="proofOfPayment" wire:model="proofOfPayment" accept=".pdf,image/*" />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Upload a screenshot or PDF receipt of your transfer (PDF, JPG or PNG, max 5MB). Your submission will be reviewed by staff before it's approved.</p>
                <p wire:loading wire:target="proofOfPayment" class="mt-2 text-xs font-medium text-brand-600 dark:text-brand-400">Uploading…</p>
                <x-input-error :messages="$errors->get('proofOfPayment')" class="mt-2" />
            </div>
        </div>
    @endif
</div>
