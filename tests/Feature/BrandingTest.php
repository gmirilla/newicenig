<?php

use App\Services\Branding\BrandingPresenter;
use App\Settings\BrandingSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('falls back to the bundled default logo and favicon when unconfigured', function () {
    $presenter = app(BrandingPresenter::class);

    expect($presenter->logoUrl())->toContain('icen-mark.jpg');
    expect($presenter->faviconUrl())->toContain('icen-mark.jpg');
});

it('resolves an uploaded logo and favicon from the public disk', function () {
    Storage::fake('public');
    Storage::disk('public')->putFileAs('branding', UploadedFile::fake()->image('logo.png'), 'logo.png');
    Storage::disk('public')->putFileAs('branding', UploadedFile::fake()->image('favicon.png'), 'favicon.png');

    $settings = app(BrandingSettings::class);
    $settings->logo_path = 'branding/logo.png';
    $settings->favicon_path = 'branding/favicon.png';
    $settings->save();

    $presenter = app(BrandingPresenter::class);

    expect($presenter->logoUrl())->toContain('branding/logo.png');
    expect($presenter->faviconUrl())->toContain('branding/favicon.png');
});

it('falls back to the logo when no favicon is configured', function () {
    Storage::fake('public');
    Storage::disk('public')->putFileAs('branding', UploadedFile::fake()->image('logo.png'), 'logo.png');

    $settings = app(BrandingSettings::class);
    $settings->logo_path = 'branding/logo.png';
    $settings->favicon_path = null;
    $settings->save();

    expect(app(BrandingPresenter::class)->faviconUrl())->toContain('branding/logo.png');
});

it('emits the configured brand color as a css variable override', function () {
    $settings = app(BrandingSettings::class);
    $settings->color_brand = '#1234ab';
    $settings->save();

    expect(app(BrandingPresenter::class)->cssVariables())->toContain('--color-brand-500: #1234ab;');
});

it('renders the branding style and welcome page using configured values', function () {
    $settings = app(BrandingSettings::class);
    $settings->color_brand = '#1234ab';
    $settings->brand_short_name = 'TESTORG';
    $settings->hero_headline = 'A totally different headline';
    $settings->hero_cta_label = 'Join now';
    $settings->footer_tagline = 'A custom footer tagline.';
    $settings->site_tagline = 'A custom meta description.';
    $settings->save();

    $response = $this->get('/');

    $response->assertSee('--color-brand-500: #1234ab;', false);
    $response->assertSee('TESTORG');
    $response->assertSee('A totally different headline');
    $response->assertSee('Join now');
    $response->assertSee('A custom footer tagline.');
    $response->assertSee('A custom meta description.');
});
