<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.org_name', 'Institute of Chartered Economists of Nigeria');
        $this->migrator->add('general.org_phone', '+234 800 000 0000');
        $this->migrator->add('general.org_email', 'info@icen.test');
        $this->migrator->add('general.org_address', 'Plot 1, Economists House, Central Business District, Abuja, Nigeria');
        $this->migrator->add('general.facebook_url', null);
        $this->migrator->add('general.twitter_url', null);
        $this->migrator->add('general.linkedin_url', null);
    }
};
