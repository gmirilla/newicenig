<?php

namespace App\Enums;

enum MembershipStatus: string
{
    case PendingPayment = 'pending_payment';
    case PendingReview = 'pending_review';
    case Active = 'active';
    case Expired = 'expired';
    case Revoked = 'revoked';
    case Superseded = 'superseded';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Pending payment',
            self::PendingReview => 'Pending review',
            self::Active => 'Active',
            self::Expired => 'Expired',
            self::Revoked => 'Revoked',
            self::Superseded => 'Superseded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PendingPayment, self::PendingReview => 'warning',
            self::Active => 'success',
            self::Expired, self::Superseded => 'gray',
            self::Revoked => 'danger',
        };
    }
}
