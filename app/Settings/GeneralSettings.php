<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $org_name;

    public string $org_phone;

    public string $org_email;

    public string $org_address;

    public ?string $facebook_url;

    public ?string $twitter_url;

    public ?string $linkedin_url;

    /**
     * Internal staff addresses notified whenever a membership payment or renewal
     * succeeds. Each notification includes a PDF of the member's information.
     *
     * @var array<int, string>
     */
    public array $payment_notification_recipients;

    /**
     * How many days before a membership's expiry to send a renewal reminder.
     * One reminder is sent per value, e.g. [30, 7] sends a reminder 30 days
     * out and again 7 days out.
     *
     * @var array<int, int>
     */
    public array $membership_expiry_reminder_days;

    public static function group(): string
    {
        return 'general';
    }
}
