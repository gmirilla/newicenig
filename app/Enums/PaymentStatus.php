<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case PendingVerification = 'pending_verification';
    case Successful = 'successful';
    case Failed = 'failed';
    case Abandoned = 'abandoned';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::PendingVerification => 'Pending verification',
            self::Successful => 'Successful',
            self::Failed => 'Failed',
            self::Abandoned => 'Abandoned',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending, self::PendingVerification => 'warning',
            self::Successful => 'success',
            self::Failed, self::Abandoned => 'danger',
        };
    }
}
