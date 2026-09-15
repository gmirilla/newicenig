<x-layouts.marketing>
    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white">
        <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand-600/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -left-16 bottom-0 h-72 w-72 rounded-full bg-brand-950/40 blur-2xl"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <p class="inline-flex items-center gap-2 rounded-full border border-accent-400/40 bg-accent-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-accent-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent-400"></span>
                    Chartered. Certified. Trusted.
                </p>

                <h1 class="mt-6 text-4xl font-extrabold tracking-tight sm:text-6xl">
                    The professional home for Nigeria's economists
                </h1>

                <p class="mt-6 text-lg leading-8 text-brand-100">
                    ICEN certifies, develops, and represents professional economists across Nigeria — setting the
                    standard for economic practice through membership, continuing education, and advocacy.
                </p>

                <div class="mt-10 flex items-center justify-center gap-4">
                    <x-button href="{{ Route::has('join') ? route('join') : '#' }}" variant="accent" size="lg">Become a member</x-button>
                    <x-button href="https://new.icennig.org.ng/" variant="secondary" size="lg" class="!bg-white/10 !text-white !ring-white/30 hover:!bg-white/20">Learn more</x-button>
                </div>
            </div>
        </div>
    </section>


    <!-- How it works -->
<section class="bg-white py-16 lg:py-24 border-t border-green-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center mb-12">
                <span class="text-green-700 font-semibold text-sm uppercase tracking-widest">Process</span>
                <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-slate-900">How It Works</h2>
            </div>

            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                                                <div class="relative flex flex-col items-start">
                    <div class="text-5xl font-extrabold text-green-100 select-none leading-none mb-3">01</div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Choose Category</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">Select the membership grade that best matches your professional qualification and experience.</p>
                </div>
                                <div class="relative flex flex-col items-start">
                    <div class="text-5xl font-extrabold text-green-100 select-none leading-none mb-3">02</div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Fill the Form</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">Complete the online registration form with your personal and professional details.</p>
                </div>
                                <div class="relative flex flex-col items-start">
                    <div class="text-5xl font-extrabold text-green-100 select-none leading-none mb-3">03</div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Make Payment</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">Pay securely online via Paystack. Your information is protected throughout.</p>
                </div>
                                <div class="relative flex flex-col items-start">
                    <div class="text-5xl font-extrabold text-green-100 select-none leading-none mb-3">04</div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Get Verified</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">Receive your membership credentials by email after review and approval by ICEN.</p>
                </div>
                            </div>
        </div>
    </section>



    <!-- CTA -->
    <section class="bg-brand-surface">
        <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold tracking-tight text-white">Ready to join?</h2>
            <p class="mt-4 text-accent-200">
                Applications are reviewed by our registration committee.
            </p>
            <div class="mt-8">
                <x-button href="{{ Route::has('join') ? route('join') : '#' }}" variant="accent" size="lg">Start your application</x-button>
            </div>
        </div>
    </section>
</x-layouts.marketing>
