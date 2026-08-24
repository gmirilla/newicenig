<footer class="border-t border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-gray-900">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/icen-mark.jpg') }}" alt="ICEN" class="h-10 w-10 rounded-full object-cover">
                    <span class="text-lg font-bold text-gray-900 dark:text-white">ICEN</span>
                </div>
                <p class="mt-4 max-w-sm text-sm text-gray-600 dark:text-gray-400">
                    The Institute of Chartered Economists of Nigeria — advancing the economics profession through certification, standards, and continuing professional development.
                </p>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Organization</h3>
                <ul class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li><a href="{{ Route::has('about') ? route('about') : '#' }}" class="hover:text-brand-600 dark:hover:text-brand-400">About us</a></li>
                    <li><a href="{{ Route::has('leadership') ? route('leadership') : '#' }}" class="hover:text-brand-600 dark:hover:text-brand-400">Leadership</a></li>
                    <li><a href="{{ Route::has('membership') ? route('membership') : '#' }}" class="hover:text-brand-600 dark:hover:text-brand-400">Membership</a></li>
                    <li><a href="{{ Route::has('contact') ? route('contact') : '#' }}" class="hover:text-brand-600 dark:hover:text-brand-400">Contact</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Resources</h3>
                <ul class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li><a href="{{ Route::has('news.index') ? route('news.index') : '#' }}" class="hover:text-brand-600 dark:hover:text-brand-400">News</a></li>
                    <li><a href="{{ Route::has('events.index') ? route('events.index') : '#' }}" class="hover:text-brand-600 dark:hover:text-brand-400">Events &amp; CPD</a></li>
                    <li><a href="{{ Route::has('resources.index') ? route('resources.index') : '#' }}" class="hover:text-brand-600 dark:hover:text-brand-400">Downloads</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-gray-200 pt-6 sm:flex-row dark:border-white/10">
            <p class="text-sm text-gray-500 dark:text-gray-400">&copy; {{ date('Y') }} Institute of Chartered Economists of Nigeria. All rights reserved.</p>
        </div>
    </div>
</footer>
