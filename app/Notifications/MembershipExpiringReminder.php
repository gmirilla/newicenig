<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\UserMembership;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MembershipExpiringReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected UserMembership $membership, protected int $daysRemaining) {}

    /**
     * Members with no linked account (or an email-route "notifiable" built
     * for one, see SendMembershipExpiryReminders) have nowhere to write a
     * database notification, so only real Users get that channel.
     */
    public function via(object $notifiable): array
    {
        return $notifiable instanceof User ? ['mail', 'database'] : ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // A member with no account (or an unclaimed one) can't reach the
        // login-gated member portal, so send them to the public lookup page.
        $renewUrl = $notifiable instanceof User ? route('member.renew') : route('renew.lookup');

        return (new MailMessage)
            ->subject("Your ICEN membership expires in {$this->daysRemaining} days")
            ->greeting("Hi {$this->membership->fullName()},")
            ->line("Your {$this->membership->membershipTier->name} membership expires on {$this->membership->expires_at->format('F j, Y')}.")
            ->line('Renew now to keep your certification and member benefits active without interruption.')
            ->action('Renew my membership', $renewUrl);
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
