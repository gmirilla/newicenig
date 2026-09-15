<?php

namespace App\Notifications;

use App\Models\UserMembership;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipExpiringReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected UserMembership $membership, protected int $daysRemaining) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your ICEN membership expires in {$this->daysRemaining} days")
            ->greeting("Hi {$notifiable->name},")
            ->line("Your {$this->membership->membershipTier->name} membership expires on {$this->membership->expires_at->format('F j, Y')}.")
            ->line('Renew now to keep your certification and member benefits active without interruption.')
            ->action('Renew my membership', route('member.renew'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => "Membership expires in {$this->daysRemaining} days",
            'body' => "Your {$this->membership->membershipTier->name} membership expires on {$this->membership->expires_at->format('F j, Y')}. Renew now to avoid interruption.",
            'url' => route('member.renew'),
        ];
    }
}
