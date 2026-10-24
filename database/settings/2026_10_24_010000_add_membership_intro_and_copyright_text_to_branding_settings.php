<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('branding.membership_intro', 'ICEN membership recognises professional competence in economics and gives you access to certification, continuing education, and a national network of peers.');
        $this->migrator->add('branding.copyright_text', 'Institute of Chartered Economists of Nigeria. All rights reserved.');
    }
};
