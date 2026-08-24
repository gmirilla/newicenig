<?php

namespace App\Notifications;

use App\Models\UserMembership;
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
        return (new MailMessage)
            ->subject('Your ICEN membership has been approved')
            ->greeting("Congratulations, {$notifiable->name}!")
            ->line("Your {$this->membership->membershipTier->name} membership application has been approved.")
            ->line("Your membership number is: {$this->membership->membership_number}")
            ->action('View your dashboard', route('member.dashboard'))
            ->line('Thank you for being part of ICEN.');
    }
}
