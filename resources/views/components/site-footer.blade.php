<footer class="bg-brand-surface text-accent-100">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/icen-mark.jpg') }}" alt="ICEN" class="h-10 w-10 rounded-full object-cover ring-2 ring-accent-400/30">
                    <span class="text-lg font-bold text-white">ICEN</span>
                </div>
                <p class="mt-4 max-w-sm text-sm text-accent-100/80">
                    The Institute of Chartered Economists of Nigeria — advancing the economics profession through certification, standards, and continuing professional development.
                </p>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-accent-300">Organization</h3>
                <ul class="mt-4 space-y-2 text-sm text-accent-100/80">
                    <li><a href="{{ Route::has('about') ? route('about') : '#' }}" class="hover:text-accent-300">About us</a></li>
                    <li><a href="{{ Route::has('leadership') ? route('leadership') : '#' }}" class="hover:text-accent-300">Leadership</a></li>
                    <li><a href="{{ Route::has('membership') ? route('membership') : '#' }}" class="hover:text-accent-300">Membership</a></li>
                    <li><a href="{{ Route::has('contact') ? route('contact') : '#' }}" class="hover:text-accent-300">Contact</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-accent-300">Resources</h3>
                <ul class="mt-4 space-y-2 text-sm text-accent-100/80">
                    <li><a href="{{ Route::has('news.index') ? route('news.index') : '#' }}" class="hover:text-accent-300">News</a></li>
                    <li><a href="{{ Route::has('events.index') ? route('events.index') : '#' }}" class="hover:text-accent-300">Events &amp; CPD</a></li>
                    <li><a href="{{ Route::has('resources.index') ? route('resources.index') : '#' }}" class="hover:text-accent-300">Downloads</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-accent-800 pt-6 sm:flex-row">
            <p class="text-sm text-accent-300/80">&copy; {{ date('Y') }} Institute of Chartered Economists of Nigeria. All rights reserved.</p>
        </div>
    </div>
</footer>
