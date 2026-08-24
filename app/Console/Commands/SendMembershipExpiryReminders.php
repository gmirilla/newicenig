<?php

namespace App\Console\Commands;

use App\Enums\MembershipStatus;
use App\Models\UserMembership;
use App\Notifications\MembershipExpiringReminder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:send-membership-expiry-reminders')]
#[Description('Email members whose membership expires in 30 or 7 days')]
class SendMembershipExpiryReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        foreach ([30, 7] as $daysRemaining) {
            $memberships = UserMembership::query()
                ->where('status', MembershipStatus::Active)
                ->whereNotNull('user_id')
                ->whereDate('expires_at', now()->addDays($daysRemaining)->toDateString())
                ->with('user')
                ->get();

            foreach ($memberships as $membership) {
                $membership->user->notify(new MembershipExpiringReminder($membership, $daysRemaining));
            }

            $this->info("Sent {$memberships->count()} reminder(s) for memberships expiring in {$daysRemaining} days.");
        }
    }
}
