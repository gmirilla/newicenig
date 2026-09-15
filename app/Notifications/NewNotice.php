<?php

namespace App\Notifications;

use App\Models\Notice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewNotice extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Notice $notice) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->notice->title,
            'body' => Str::limit(strip_tags($this->notice->body), 140),
            'url' => route('member.notices'),
        ];
    }
}
