<?php

namespace App\Enums;

enum MembershipStatus: string
{
    case PendingPayment = 'pending_payment';
    case PendingReview = 'pending_review';
    case Active = 'active';
    case Expired = 'expired';
    case Revoked = 'revoked';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Pending payment',
            self::PendingReview => 'Pending review',
            self::Active => 'Active',
            self::Expired => 'Expired',
            self::Revoked => 'Revoked',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PendingPayment, self::PendingReview => 'warning',
            self::Active => 'success',
            self::Expired => 'gray',
            self::Revoked => 'danger',
        };
    }
}
