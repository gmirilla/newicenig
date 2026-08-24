<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class SetPasswordInvite extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'password.set',
            now()->addDays(3),
            ['user' => $notifiable->getKey()],
        );

        return (new MailMessage)
            ->subject('Welcome to ICEN — set your password')
            ->greeting("Welcome, {$notifiable->name}!")
            ->line('Your ICEN membership application and payment were received. An account has been created for you.')
            ->action('Set your password', $url)
            ->line('This link expires in 3 days. If you did not expect this email, you can ignore it.');
    }
}
