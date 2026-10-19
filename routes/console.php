<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:send-membership-expiry-reminders')->daily();

// Backstop for CheckPendingPaystackPayment's queued retry chain — catches
// payments whose next check is overdue because a queue-worker cron run was
// missed. Runs independently of the queue, so it still works when that
// cron doesn't.
Schedule::command('app:recheck-pending-paystack-payments')->everyFiveMinutes();

// Proves the scheduler cron itself is still alive, by pinging an external
// dead man's switch (set SCHEDULE_HEARTBEAT_URL to enable — see
// config/services.php). If this cron entry stops firing entirely, nothing
// above can notice on its own; the external monitor alerts on the ping's
// absence instead. No-ops when unconfigured.
Schedule::command('app:ping-scheduler-heartbeat')
    ->everyMinute()
    ->when(fn () => filled(config('services.scheduler_heartbeat.url')))
    ->onOneServer();
