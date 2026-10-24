<?php

namespace App\Notifications;

use App\Models\UserMembership;
use App\Settings\BrandingSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipReinstated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected UserMembership $membership) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your '.app(BrandingSettings::class)->brand_short_name.' membership has been reinstated')
            ->greeting("Dear {$notifiable->name},")
            ->line("Your {$this->membership->membershipTier->name} membership has been reinstated, and your access to the member portal has been restored.")
            ->action('View your dashboard', route('member.dashboard'));
    }
}
