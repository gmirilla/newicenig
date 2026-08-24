<div>
    <div class="mb-8 flex items-center justify-center gap-2">
        @foreach ([1 => 'Tier', 2 => 'Details', 3 => 'Review & pay'] as $number => $label)
            <div class="flex items-center gap-2">
                <div @class([
                    'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold',
                    'bg-brand-600 text-white' => $step >= $number,
                    'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-gray-400' => $step < $number,
                ])>
                    {{ $number }}
                </div>
                <span class="hidden text-sm text-gray-600 sm:block dark:text-gray-400">{{ $label }}</span>
            </div>
            @if ($number < 3)
                <div class="h-px w-8 bg-gray-200 dark:bg-white/10"></div>
            @endif
        @endforeach
    </div>

    @if ($step === 1)
        <div class="grid gap-6 sm:grid-cols-2">
            @forelse (\App\Models\MembershipTier::where('is_active', true)->orderBy('sort_order')->get() as $tier)
                <x-card class="flex h-full flex-col justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $tier->name }}</h3>
                        @if ($tier->abbreviation)
                            <p class="text-sm text-brand-600 dark:text-brand-400">{{ $tier->abbreviation }}</p>
                        @endif
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $tier->description }}</p>
                        <p class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $tier->currency }} {{ number_format($tier->registration_fee, 0) }}
                        </p>
                    </div>
                    <x-button wire:click="selectTier({{ $tier->id }})" class="mt-6 w-full justify-center">
                        Choose {{ $tier->name }}
                    </x-button>
                </x-card>
            @empty
                <p class="col-span-2 text-center text-sm text-gray-500 dark:text-gray-400">
                    No membership tiers are open for registration right now. Please check back soon.
                </p>
            @endforelse
        </div>
    @endif

    @if ($step === 2)
        <x-card>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Applicant details</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Applying for: <strong>{{ $this->selectedTier?->name }}</strong></p>

            <div class="mt-6 grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="first_name" value="First name" />
                    <x-text-input id="first_name" class="mt-1 block w-full" wire:model="first_name" required />
                    <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="last_name" value="Last name" />
                    <x-text-input id="last_name" class="mt-1 block w-full" wire:model="last_name" required />
                    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" type="email" class="mt-1 block w-full" wire:model="email" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="phone" value="Phone" />
                    <x-text-input id="phone" class="mt-1 block w-full" wire:model="phone" required />
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="state_of_origin" value="State of origin" />
                    <x-text-input id="state_of_origin" class="mt-1 block w-full" wire:model="state_of_origin" />
                </div>
                <div>
                    <x-input-label for="qualification" value="Highest qualification" />
                    <x-text-input id="qualification" class="mt-1 block w-full" wire:model="qualification" />
                </div>
                <div>
                    <x-input-label for="date_of_birth" value="Date of birth" />
                    <x-text-input id="date_of_birth" type="date" class="mt-1 block w-full" wire:model="date_of_birth" />
                    <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
                </div>

                @if ($this->selectedTier?->requires_employer_info)
                    <div>
                        <x-input-label for="employer_name" value="Employer" />
                        <x-text-input id="employer_name" class="mt-1 block w-full" wire:model="employer_name" required />
                        <x-input-error :messages="$errors->get('employer_name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="job_title" value="Job title" />
                        <x-text-input id="job_title" class="mt-1 block w-full" wire:model="job_title" required />
                        <x-input-error :messages="$errors->get('job_title')" class="mt-2" />
                    </div>
                @endif

                @if ($this->selectedTier?->requires_qualification_upload)
                    <div class="sm:col-span-2">
                        <x-input-label for="qualification_document" value="Qualification certificate (PDF or image)" />
                        <input id="qualification_document" type="file" wire:model="qualification_document" class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-400">
                        <x-input-error :messages="$errors->get('qualification_document')" class="mt-2" />
                    </div>
                @endif

                <div class="sm:col-span-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="directory_opt_in" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-gray-600 dark:text-gray-400">List me in the public member directory once approved (optional)</span>
                    </label>
                </div>
            </div>

            <div class="mt-8 flex justify-between">
                <x-button wire:click="$set('step', 1)" variant="secondary">Back</x-button>
                <x-button wire:click="continueToReview">Continue to review</x-button>
            </div>
        </x-card>
    @endif

    @if ($step === 3)
        <x-card>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Review & pay</h3>

            <dl class="mt-6 divide-y divide-gray-200 text-sm dark:divide-white/10">
                <div class="flex justify-between py-3">
                    <dt class="text-gray-500 dark:text-gray-400">Membership tier</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">{{ $this->selectedTier?->name }}</dd>
                </div>
                <div class="flex justify-between py-3">
                    <dt class="text-gray-500 dark:text-gray-400">Applicant</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">{{ $first_name }} {{ $last_name }}</dd>
                </div>
                <div class="flex justify-between py-3">
                    <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">{{ $email }}</dd>
                </div>
                <div class="flex justify-between py-3 text-base">
                    <dt class="font-semibold text-gray-900 dark:text-white">Amount due</dt>
                    <dd class="font-semibold text-gray-900 dark:text-white">
                        {{ $this->selectedTier?->currency }} {{ number_format($this->selectedTier?->registration_fee ?? 0, 2) }}
                    </dd>
                </div>
            </dl>

            <div class="mt-8 flex justify-between">
                <x-button wire:click="$set('step', 2)" variant="secondary">Back</x-button>
                <x-button wire:click="submitAndPay" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submitAndPay">Pay & submit application</span>
                    <span wire:loading wire:target="submitAndPay">Redirecting to Paystack…</span>
                </x-button>
            </div>
        </x-card>
    @endif
</div>
