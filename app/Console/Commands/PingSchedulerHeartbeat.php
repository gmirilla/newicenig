<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pings an external "dead man's switch" (e.g. a healthchecks.io check) every
 * time it runs. Nothing scheduled by cron can detect cron itself dying —
 * that silence has to be noticed from the outside — so this is what lets an
 * external monitor alert someone when `schedule:run` stops firing at all,
 * which would otherwise silently stop both the Paystack payment sweep (see
 * App\Actions\Payments\CheckPendingPaystackPayment) and the daily membership
 * expiry reminders along with it.
 *
 * A failed or slow ping is logged but never fails the command — the
 * monitoring service is what tracks liveness by the ping's *absence*, so
 * throwing here would only risk polluting the failed-jobs list for a
 * problem the external service already handles.
 */
#[Signature('app:ping-scheduler-heartbeat')]
#[Description("Ping the configured dead man's switch URL to prove the scheduler is still running")]
class PingSchedulerHeartbeat extends Command
{
    public function handle(): void
    {
        $url = config('services.scheduler_heartbeat.url');

        if (blank($url)) {
            return;
        }

        try {
            Http::timeout(5)->get($url);
        } catch (\Throwable $e) {
            Log::warning('Scheduler heartbeat ping failed: '.$e->getMessage());
        }
    }
}
