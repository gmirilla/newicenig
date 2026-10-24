<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // These are this deployment's current, previously-hardcoded ICEN
        // content — kept as the defaults so nothing changes visually until
        // someone deliberately edits Branding settings in the admin panel.
        $this->migrator->add('branding.logo_path', null);
        $this->migrator->add('branding.favicon_path', null);
        $this->migrator->add('branding.brand_short_name', 'ICEN');
        $this->migrator->add('branding.color_brand', '#16a34a');
        $this->migrator->add('branding.color_accent', '#f59e0b');
        $this->migrator->add('branding.color_surface', '#07412d');
        $this->migrator->add('branding.hero_eyebrow', 'Chartered. Certified. Trusted.');
        $this->migrator->add('branding.hero_headline', "The professional home for Nigeria's economists");
        $this->migrator->add('branding.hero_subheadline', 'ICEN certifies, develops, and represents professional economists across Nigeria — setting the standard for economic practice through membership, continuing education, and advocacy.');
        $this->migrator->add('branding.hero_cta_label', 'Become a member');
        $this->migrator->add('branding.footer_tagline', 'The Institute of Chartered Economists of Nigeria — advancing the economics profession through certification, standards, and continuing professional development.');
        $this->migrator->add('branding.site_tagline', null);
    }
};
