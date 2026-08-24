<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PaystackSettings extends Settings
{
    public string $public_key;

    public string $secret_key;

    public string $payment_url;

    public static function group(): string
    {
        return 'paystack';
    }

    public static function encrypted(): array
    {
        return ['secret_key'];
    }
}
