<?php

namespace App\Enums;

enum EmailLogStatus: string
{
    case Sending = 'sending';
    case Sent = 'sent';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Sending => 'Sending',
            self::Sent => 'Sent',
            self::Failed => 'Failed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Sending => 'warning',
            self::Sent => 'success',
            self::Failed => 'danger',
        };
    }
}
