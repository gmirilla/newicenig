<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Paystack = 'paystack';
    case BankTransfer = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::Paystack => 'Pay online (card/bank via Paystack)',
            self::BankTransfer => 'Bank transfer',
        };
    }
}
