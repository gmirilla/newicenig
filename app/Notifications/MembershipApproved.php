<?php

namespace App\Notifications;

use App\Models\UserMembership;
use App\Settings\BrandingSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected UserMembership $membership) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isLevelChange = $this->membership->previous_membership_id !== null;
        $brandShortName = app(BrandingSettings::class)->brand_short_name;

        return (new MailMessage)
            ->subject($isLevelChange ? "Your {$brandShortName} membership level change has been approved" : "Your {$brandShortName} membership has been approved")
            ->greeting("Congratulations, {$notifiable->name}!")
            ->line($isLevelChange
                ? "Your application to change your membership to the {$this->membership->membershipTier->name} tier has been approved."
                : "Your {$this->membership->membershipTier->name} membership application has been approved.")
            ->line("Your membership number is: {$this->membership->membership_number}")
            ->action('View your dashboard', route('member.dashboard'))
            ->line("Thank you for being part of {$brandShortName}.");
    }
}
