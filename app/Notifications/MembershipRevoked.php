<?php

namespace App\Notifications;

use App\Models\UserMembership;
use App\Settings\BrandingSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipRevoked extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected UserMembership $membership) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $brandShortName = app(BrandingSettings::class)->brand_short_name;

        $message = (new MailMessage)
            ->subject("Your {$brandShortName} membership has been revoked")
            ->greeting("Dear {$notifiable->name},")
            ->line("Your {$this->membership->membershipTier->name} membership has been revoked.");

        if ($this->membership->revoke_reason) {
            $message->line("Reason given: {$this->membership->revoke_reason}");
        }

        return $message->line("If you believe this is in error, please contact {$brandShortName} support.");
    }
}
