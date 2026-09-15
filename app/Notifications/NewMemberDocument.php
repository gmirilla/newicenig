<?php

namespace App\Notifications;

use App\Models\MemberDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewMemberDocument extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected MemberDocument $document) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New document available: {$this->document->title}")
            ->greeting("Hi {$notifiable->name},")
            ->line("A new document has been added to your ICEN member account: \"{$this->document->title}\".")
            ->action('View my documents', route('member.documents'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => "New document: {$this->document->title}",
            'body' => $this->document->description
                ? Str::limit($this->document->description, 140)
                : 'A new document has been added to your account.',
            'url' => route('member.documents'),
        ];
    }
}
