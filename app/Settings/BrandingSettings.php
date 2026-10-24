<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Everything a new client deployment needs to re-skin without touching
 * code: logo, favicon, the brand/accent/surface colors (see
 * App\Services\Branding\ColorRamp — each hex here generates a full 50–950
 * scale), and the handful of homepage/footer text fields most worth
 * customizing. Defaults (in the settings migration) are this deployment's
 * current ICEN content, so an unconfigured install looks exactly as it did
 * before this existed.
 */
class BrandingSettings extends Settings
{
    /** Relative path on the public disk, e.g. "branding/logo.png". Null falls back to the bundled default. */
    public ?string $logo_path;

    /** Relative path on the public disk. Null falls back to the logo. */
    public ?string $favicon_path;

    /** Short wordmark shown next to the logo (nav, footer, PDFs) — distinct from GeneralSettings::$org_name, which is the full legal name. */
    public string $brand_short_name;

    public string $color_brand;

    public string $color_accent;

    public string $color_surface;

    public ?string $hero_eyebrow;

    public string $hero_headline;

    public string $hero_subheadline;

    public string $hero_cta_label;

    public string $footer_tagline;

    /** Used as the site's meta description. */
    public ?string $site_tagline;

    /** The intro paragraph under "Join the Institute" on the membership page. */
    public string $membership_intro;

    /** Follows "© {year} " in the footer — kept separate from GeneralSettings::$org_name since the legal copyright holder can differ from the public-facing name. */
    public string $copyright_text;

    public static function group(): string
    {
        return 'branding';
    }
}
