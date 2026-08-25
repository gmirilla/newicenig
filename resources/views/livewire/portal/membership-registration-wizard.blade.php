@php
    $stepLabels = [
        1 => 'Membership tier',
        2 => 'Personal details',
        3 => 'Contact & address',
        4 => 'Educational qualifications',
        5 => 'Professional background',
        6 => 'Declaration',
        7 => 'Review & pay',
    ];
@endphp

<div>
    <!-- Progress -->
    <div class="mb-10">
        <div class="flex items-center justify-between text-sm">
            <span class="font-semibold text-brand-700 dark:text-brand-400">Step {{ $step }} of {{ $totalSteps }}</span>
            <span class="text-slate-500 dark:text-slate-400">{{ $stepLabels[$step] }}</span>
        </div>
        <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-white/10">
            <div class="h-full rounded-full bg-brand-600 transition-all duration-300" style="width: {{ ($step / $totalSteps) * 100 }}%"></div>
        </div>
    </div>

    {{-- Step 1: Tier --}}
    @if ($step === 1)
        <div class="grid gap-6 sm:grid-cols-2">
            @forelse (\App\Models\MembershipTier::where('is_active', true)->orderBy('sort_order')->get() as $tier)
                <x-card class="flex h-full flex-col justify-between">
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
                    <x-button wire:click="selectTier({{ $tier->id }})" class="mt-6 w-full justify-center">
                        Choose {{ $tier->name }}
                    </x-button>
                </x-card>
            @empty
                <p class="col-span-2 text-center text-sm text-slate-500 dark:text-slate-400">
                    No membership tiers are open for registration right now. Please check back soon.
                </p>
            @endforelse
        </div>
    @endif

    {{-- Step 2: Personal details --}}
    @if ($step === 2)
        <x-card>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Personal details</h3>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Applying for: <strong>{{ $this->selectedTier?->name }}</strong></p>

            <div class="mt-6 grid gap-6 sm:grid-cols-3">
                <div>
                    <x-input-label for="first_name" value="First name" />
                    <x-text-input id="first_name" class="mt-1 block w-full" wire:model="first_name" required />
                    <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="middle_name" value="Middle name" />
                    <x-text-input id="middle_name" class="mt-1 block w-full" wire:model="middle_name" />
                </div>
                <div>
                    <x-input-label for="last_name" value="Last name" />
                    <x-text-input id="last_name" class="mt-1 block w-full" wire:model="last_name" required />
                    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="gender" value="Gender" />
                    <select id="gender" wire:model="gender" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                        <option value="">Select…</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                    <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="date_of_birth" value="Date of birth" />
                    <x-text-input id="date_of_birth" type="date" class="mt-1 block w-full" wire:model="date_of_birth" required />
                    <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="marital_status" value="Marital status" />
                    <select id="marital_status" wire:model="marital_status" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                        <option value="">Select…</option>
                        <option value="single">Single</option>
                        <option value="married">Married</option>
                        <option value="divorced">Divorced</option>
                        <option value="widowed">Widowed</option>
                    </select>
                    <x-input-error :messages="$errors->get('marital_status')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="place_of_birth" value="Place of birth" />
                    <x-text-input id="place_of_birth" class="mt-1 block w-full" wire:model="place_of_birth" />
                </div>
                <div>
                    <x-input-label for="nationality" value="Nationality" />
                    <x-text-input id="nationality" class="mt-1 block w-full" wire:model="nationality" required />
                    <x-input-error :messages="$errors->get('nationality')" class="mt-2" />
                </div>

                <div class="sm:col-span-3">
                    <x-input-label for="passport_photo" value="Passport photograph" />
                    <input id="passport_photo" type="file" wire:model="passport_photo" accept="image/*" class="mt-1 block w-full text-sm text-slate-600 dark:text-slate-400">
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">A recent passport-style photo, used on your membership certificate.</p>
                    <p wire:loading wire:target="passport_photo" class="mt-2 flex items-center gap-2 text-xs font-medium text-brand-600 dark:text-brand-400">
                        <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Uploading…
                    </p>
                    @if ($passport_photo)
                        <img src="{{ $passport_photo->temporaryUrl() }}" alt="Passport preview" class="mt-3 h-24 w-24 rounded-md object-cover ring-1 ring-slate-200">
                    @endif
                    <x-input-error :messages="$errors->get('passport_photo')" class="mt-2" />
                </div>
            </div>

            <div class="mt-8 flex justify-between">
                <x-button wire:click="$set('step', 1)" variant="secondary">Back</x-button>
                <x-button wire:click="continuePersonalDetails" wire:loading.attr="disabled" wire:target="passport_photo,continuePersonalDetails">Continue</x-button>
            </div>
        </x-card>
    @endif

    {{-- Step 3: Contact & address --}}
    @if ($step === 3)
        <x-card>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Contact & address</h3>

            <div class="mt-6 grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" type="email" class="mt-1 block w-full" wire:model="email" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="phone" value="Telephone" />
                    <x-text-input id="phone" class="mt-1 block w-full" wire:model="phone" required />
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="state_of_origin" value="State of origin" />
                    <x-text-input id="state_of_origin" class="mt-1 block w-full" wire:model="state_of_origin" required />
                    <x-input-error :messages="$errors->get('state_of_origin')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="local_government_area" value="Local government area" />
                    <x-text-input id="local_government_area" class="mt-1 block w-full" wire:model="local_government_area" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="residential_address" value="Residential address" />
                    <textarea id="residential_address" wire:model="residential_address" rows="2" required
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"></textarea>
                    <x-input-error :messages="$errors->get('residential_address')" class="mt-2" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="postal_address" value="Postal address (if different)" />
                    <textarea id="postal_address" wire:model="postal_address" rows="2"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"></textarea>
                </div>

                <div>
                    <x-input-label for="next_of_kin_name" value="Next of kin — name" />
                    <x-text-input id="next_of_kin_name" class="mt-1 block w-full" wire:model="next_of_kin_name" />
                </div>
                <div>
                    <x-input-label for="next_of_kin_address" value="Next of kin — address" />
                    <x-text-input id="next_of_kin_address" class="mt-1 block w-full" wire:model="next_of_kin_address" />
                </div>
            </div>

            <div class="mt-8 flex justify-between">
                <x-button wire:click="$set('step', 2)" variant="secondary">Back</x-button>
                <x-button wire:click="continueContactAddress">Continue</x-button>
            </div>
        </x-card>
    @endif

    {{-- Step 4: Educational qualifications --}}
    @if ($step === 4)
        <x-card>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Educational qualifications</h3>

            <div class="mt-6 space-y-8">
                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Primary school</h4>
                    <div class="mt-3 grid gap-4 sm:grid-cols-3">
                        <x-text-input placeholder="School name" class="block w-full" wire:model="primary_school" />
                        <x-text-input placeholder="Year passed out" class="block w-full" wire:model="primary_school_year" />
                        <div>
                            <input type="file" wire:model="primary_school_certificate_file" accept=".pdf,image/*" class="block w-full text-sm text-slate-600 dark:text-slate-400">
                            <x-input-error :messages="$errors->get('primary_school_certificate_file')" class="mt-1" />
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Secondary school</h4>
                    <div class="mt-3 grid gap-4 sm:grid-cols-3">
                        <x-text-input placeholder="School name" class="block w-full" wire:model="secondary_school" />
                        <x-text-input placeholder="Year passed out" class="block w-full" wire:model="secondary_school_year" />
                        <div>
                            <input type="file" wire:model="secondary_school_certificate_file" accept=".pdf,image/*" class="block w-full text-sm text-slate-600 dark:text-slate-400">
                            <x-input-error :messages="$errors->get('secondary_school_certificate_file')" class="mt-1" />
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Higher institution</h4>
                    <div class="mt-3 grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-text-input placeholder="Institution name" class="block w-full" wire:model="higher_institution" required />
                            <x-input-error :messages="$errors->get('higher_institution')" class="mt-1" />
                        </div>
                        <div>
                            <x-text-input placeholder="Course of study" class="block w-full" wire:model="higher_institution_course" required />
                            <x-input-error :messages="$errors->get('higher_institution_course')" class="mt-1" />
                        </div>
                        <div>
                            <x-text-input placeholder="Year passed out" class="block w-full" wire:model="higher_institution_year" required />
                            <x-input-error :messages="$errors->get('higher_institution_year')" class="mt-1" />
                        </div>
                        <x-text-input placeholder="Final grade / class of degree" class="block w-full" wire:model="higher_institution_grade" />
                        <x-text-input placeholder="Second degree (if any)" class="block w-full sm:col-span-2" wire:model="higher_institution_second_degree" />
                        <div class="sm:col-span-2">
                            <x-input-label value="Certificate" />
                            <input type="file" wire:model="higher_institution_certificate_file" accept=".pdf,image/*" class="mt-1 block w-full text-sm text-slate-600 dark:text-slate-400">
                            <x-input-error :messages="$errors->get('higher_institution_certificate_file')" class="mt-1" />
                        </div>
                    </div>
                </div>
            </div>

            <p
                wire:loading
                wire:target="primary_school_certificate_file,secondary_school_certificate_file,higher_institution_certificate_file"
                class="mt-4 flex items-center gap-2 text-xs font-medium text-brand-600 dark:text-brand-400"
            >
                <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Uploading document…
            </p>

            <div class="mt-8 flex justify-between">
                <x-button wire:click="$set('step', 3)" variant="secondary">Back</x-button>
                <x-button
                    wire:click="continueEducation"
                    wire:loading.attr="disabled"
                    wire:target="primary_school_certificate_file,secondary_school_certificate_file,higher_institution_certificate_file,continueEducation"
                >Continue</x-button>
            </div>
        </x-card>
    @endif

    {{-- Step 5: Professional background --}}
    @if ($step === 5)
        <x-card>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Professional background</h3>

            <div class="mt-6 grid gap-6 sm:grid-cols-2">
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

                <div>
                    <x-input-label for="years_of_experience" value="Years of experience" />
                    <x-text-input id="years_of_experience" type="number" min="0" class="mt-1 block w-full" wire:model="years_of_experience" />
                </div>
                <div>
                    <x-input-label for="year_of_qualification" value="Year of qualification" />
                    <x-text-input id="year_of_qualification" class="mt-1 block w-full" wire:model="year_of_qualification" required />
                    <x-input-error :messages="$errors->get('year_of_qualification')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model.live="belongs_to_other_institute" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-slate-700 dark:text-slate-300">I belong to another professional institute similar to ICEN</span>
                    </label>
                </div>

                @if ($belongs_to_other_institute)
                    <div>
                        <x-input-label for="other_institute_name" value="Name of the institute" />
                        <x-text-input id="other_institute_name" class="mt-1 block w-full" wire:model="other_institute_name" required />
                        <x-input-error :messages="$errors->get('other_institute_name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="other_institute_status" value="Your status in the institute" />
                        <x-text-input id="other_institute_status" class="mt-1 block w-full" wire:model="other_institute_status" required />
                        <x-input-error :messages="$errors->get('other_institute_status')" class="mt-2" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="other_institute_membership_number" value="Membership number" />
                        <x-text-input id="other_institute_membership_number" class="mt-1 block w-full" wire:model="other_institute_membership_number" />
                    </div>
                @endif
            </div>

            <div class="mt-8 flex justify-between">
                <x-button wire:click="$set('step', 4)" variant="secondary">Back</x-button>
                <x-button wire:click="continueProfessionalBackground">Continue</x-button>
            </div>
        </x-card>
    @endif

    {{-- Step 6: Declaration --}}
    @if ($step === 6)
        <x-card>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Declaration</h3>

            <div class="mt-4 rounded-md bg-slate-50 p-4 text-sm text-slate-700 dark:bg-white/5 dark:text-slate-300">
                I declare that the information provided in this application is true and correct to the best of my
                knowledge, and I undertake to abide by the constitution, rules, and code of professional conduct of
                the Institute of Chartered Economists of Nigeria.
            </div>

            <div class="mt-6 grid gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-input-label for="declaration_name" value="Type your full name to sign" />
                    <x-text-input id="declaration_name" class="mt-1 block w-full" wire:model="declaration_name" required />
                    <x-input-error :messages="$errors->get('declaration_name')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="declaration_agreed" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-slate-700 dark:text-slate-300">I agree to the declaration above</span>
                    </label>
                    <x-input-error :messages="$errors->get('declaration_agreed')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="directory_opt_in" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-slate-600 dark:text-slate-400">List me in the public member directory once approved (optional)</span>
                    </label>
                </div>
            </div>

            <div class="mt-8 flex justify-between">
                <x-button wire:click="$set('step', 5)" variant="secondary">Back</x-button>
                <x-button wire:click="continueDeclaration">Continue to review</x-button>
            </div>
        </x-card>
    @endif

    {{-- Step 7: Review & pay --}}
    @if ($step === 7)
        <x-card>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Review & pay</h3>

            <dl class="mt-6 divide-y divide-slate-200 text-sm dark:divide-white/10">
                <div class="flex justify-between py-3">
                    <dt class="text-slate-500 dark:text-slate-400">Membership tier</dt>
                    <dd class="font-medium text-slate-900 dark:text-white">{{ $this->selectedTier?->name }}</dd>
                </div>
                <div class="flex justify-between py-3">
                    <dt class="text-slate-500 dark:text-slate-400">Applicant</dt>
                    <dd class="font-medium text-slate-900 dark:text-white">{{ $first_name }} {{ $middle_name }} {{ $last_name }}</dd>
                </div>
                <div class="flex justify-between py-3">
                    <dt class="text-slate-500 dark:text-slate-400">Email</dt>
                    <dd class="font-medium text-slate-900 dark:text-white">{{ $email }}</dd>
                </div>
                <div class="flex justify-between py-3">
                    <dt class="text-slate-500 dark:text-slate-400">Higher institution</dt>
                    <dd class="font-medium text-slate-900 dark:text-white">{{ $higher_institution }}</dd>
                </div>
                <div class="flex justify-between py-3 text-base">
                    <dt class="font-semibold text-slate-900 dark:text-white">Amount due</dt>
                    <dd class="font-semibold text-slate-900 dark:text-white">
                        {{ $this->selectedTier?->currency }} {{ number_format($this->selectedTier?->registration_fee ?? 0, 2) }}
                    </dd>
                </div>
            </dl>

            <div class="mt-8 flex justify-between">
                <x-button wire:click="$set('step', 6)" variant="secondary">Back</x-button>
                <x-button wire:click="submitAndPay" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submitAndPay">Pay & submit application</span>
                    <span wire:loading wire:target="submitAndPay">Redirecting to Paystack…</span>
                </x-button>
            </div>
        </x-card>
    @endif
</div>
