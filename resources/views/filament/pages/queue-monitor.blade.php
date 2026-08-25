@php
    $status = $this->getWorkerStatus();
@endphp

<x-filament-panels::page>
    <div class="grid gap-6 sm:grid-cols-3">
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <p class="text-sm text-gray-500 dark:text-gray-400">Worker status</p>
            <p class="mt-1 flex items-center gap-2 text-2xl font-bold text-gray-950 dark:text-white">
                <span class="h-2.5 w-2.5 rounded-full {{ $status['running'] ? 'bg-success-500' : 'bg-gray-400' }}"></span>
                {{ $status['running'] ? 'Running' : 'Stopped' }}
            </p>
            @if ($status['running'])
                <p class="mt-1 text-xs text-gray-400">PID {{ $status['pid'] }}</p>
            @endif
        </div>

        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <p class="text-sm text-gray-500 dark:text-gray-400">Pending jobs</p>
            <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ $this->getPendingCount() }}</p>
        </div>

        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <p class="text-sm text-gray-500 dark:text-gray-400">Failed jobs</p>
            <p class="mt-1 text-2xl font-bold {{ $this->getFailedCount() > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-gray-950 dark:text-white' }}">
                {{ $this->getFailedCount() }}
            </p>
        </div>
    </div>

    <div class="fi-section mt-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <h2 class="text-base font-semibold text-gray-950 dark:text-white">About worker control</h2>
        <div class="mt-2 space-y-2 text-sm text-gray-600 dark:text-gray-400">
            <p>
                <strong>Start</strong> and <strong>Stop</strong> launch or kill a single background <code>queue:work</code>
                process directly from this page — convenient for a small, single-server deployment, but not a
                substitute for a real process supervisor (Supervisor, systemd, a Windows service, or Laravel Horizon)
                in production. If the web server restarts, a worker started this way does not automatically come back.
            </p>
            <p>
                <strong>Restart</strong> is Laravel's built-in graceful signal — it tells every currently running
                worker (however it was started) to finish its current job and exit, and is always safe to use.
            </p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <a href="{{ \App\Filament\Resources\QueuedJobs\QueuedJobResource::getUrl() }}" class="fi-section block rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 transition hover:ring-primary-300 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-sm font-semibold text-gray-950 dark:text-white">View pending jobs &rarr;</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">See what's currently queued and waiting to be processed.</p>
        </a>
        <a href="{{ \App\Filament\Resources\FailedJobs\FailedJobResource::getUrl() }}" class="fi-section block rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 transition hover:ring-primary-300 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-sm font-semibold text-gray-950 dark:text-white">View failed jobs &rarr;</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Inspect exceptions, retry, or clear failed jobs.</p>
        </a>
    </div>
</x-filament-panels::page>
