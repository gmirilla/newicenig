<?php

namespace App\Console\Commands;

use App\Enums\MembershipStatus;
use App\Models\UserMembership;
use App\Notifications\MembershipExpiringReminder;
use App\Settings\GeneralSettings;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

#[Signature('app:send-membership-expiry-reminders')]
#[Description('Email and notify members whose membership is about to expire')]
class SendMembershipExpiryReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(GeneralSettings $settings): void
    {
        $reminderDays = array_map('intval', $settings->membership_expiry_reminder_days ?: [30, 7]);

        foreach ($reminderDays as $daysRemaining) {
            $memberships = UserMembership::query()
                ->where('status', MembershipStatus::Active)
                ->whereDate('expires_at', now()->addDays($daysRemaining)->toDateString())
                ->where(fn ($query) => $query->whereNotNull('user_id')->orWhereNotNull('email'))
                ->with('user')
                ->get();

            foreach ($memberships as $membership) {
                // A membership with no linked account yet (or one imported
                // without ever registering) still has the email it was
                // submitted with — route the notification straight there.
                $notifiable = $membership->user ?: Notification::route('mail', $membership->email);

                $notifiable->notify(new MembershipExpiringReminder($membership, $daysRemaining));
            }

            $this->info("Sent {$memberships->count()} reminder(s) for memberships expiring in {$daysRemaining} days.");
        }
    }
}
