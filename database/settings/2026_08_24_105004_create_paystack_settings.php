<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('paystack.public_key', (string) env('PAYSTACK_PUBLIC_KEY', ''));
        $this->migrator->addEncrypted('paystack.secret_key', (string) env('PAYSTACK_SECRET_KEY', ''));
        $this->migrator->add('paystack.payment_url', (string) env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'));
    }
};
