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
