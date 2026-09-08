<?php

namespace App\Listeners;

use App\Enums\EmailLogStatus;
use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

/**
 * Records every outgoing email into `email_logs` so the admin panel can show
 * delivery status and resend a failed one. Each `handle*` method below is
 * auto-discovered by Laravel's zero-config event discovery (on by default —
 * see `Illuminate\Foundation\Application::configure()`, which calls
 * `withEvents()`) purely from its name and typed parameter; nothing needs to
 * register this class anywhere.
 *
 * Correlates a message's `MessageSending` with whatever happens next —
 * `MessageSent` (success), or the enclosing queue job's `JobFailed` — via a
 * single "currently open" log id. This works because a queue job sends at
 * most one message before either succeeding or throwing, so there's never
 * more than one open log at a time. Mail sent outside the queue (not
 * `ShouldQueue`) still gets logged as "sending" and updated to "sent" the
 * same way, but a synchronous failure has no `JobFailed` event to catch it —
 * it will stay "sending"; check the application log for those.
 */
class LogOutgoingMail
{
    protected static ?int $openLogId = null;

    public function handleJobProcessing(JobProcessing $event): void
    {
        static::$openLogId = null;
    }

    public function handleMessageSending(MessageSending $event): void
    {
        $type = $event->data['__laravel_notification'] ?? $event->data['__laravel_mailable'] ?? null;

        if (! $type) {
            return;
        }

        static::$openLogId = EmailLog::create([
            'type' => $type,
            'to' => $this->addressesFrom($event->message->getTo()),
            'subject' => $event->message->getSubject(),
            'status' => EmailLogStatus::Sending,
        ])->id;
    }

    public function handleMessageSent(MessageSent $event): void
    {
        $this->markSent();
    }

    public function handleJobProcessed(JobProcessed $event): void
    {
        $this->markSent();
    }

    public function handleJobFailed(JobFailed $event): void
    {
        if (! static::$openLogId) {
            return;
        }

        EmailLog::whereKey(static::$openLogId)->update([
            'status' => EmailLogStatus::Failed,
            'error' => $event->exception->getMessage(),
            'failed_at' => now(),
            'failed_job_uuid' => $event->job->uuid(),
        ]);

        static::$openLogId = null;
    }

    protected function markSent(): void
    {
        if (! static::$openLogId) {
            return;
        }

        EmailLog::whereKey(static::$openLogId)->update([
            'status' => EmailLogStatus::Sent,
            'sent_at' => now(),
        ]);

        static::$openLogId = null;
    }

    /**
     * @param  array<int, \Symfony\Component\Mime\Address>  $addresses
     * @return array<int, string>
     */
    protected function addressesFrom(array $addresses): array
    {
        return collect($addresses)->map(fn ($address) => $address->getAddress())->all();
    }
}
