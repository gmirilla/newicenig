<?php

use App\Filament\Pages\ManageBrandingSettings;
use App\Models\User;
use App\Settings\BrandingSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('lets an admin view and save branding settings, but not a registrar', function () {
    Role::findOrCreate('admin');
    Role::findOrCreate('registrar');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    $this->actingAs($admin)->get('/admin/manage-branding-settings')->assertOk();
    $this->actingAs($registrar)->get('/admin/manage-branding-settings')->assertForbidden();
});

it('saves updated branding settings', function () {
    Role::findOrCreate('admin');
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(ManageBrandingSettings::class)
        ->fillForm([
            'brand_short_name' => 'NEWORG',
            'color_brand' => '#1234ab',
            'color_accent' => '#abcdef',
            'color_surface' => '#0a0a0a',
            'hero_eyebrow' => 'Trusted since day one.',
            'hero_headline' => 'A brand new headline',
            'hero_subheadline' => 'A brand new subheading.',
            'hero_cta_label' => 'Join today',
            'footer_tagline' => 'A brand new footer tagline.',
            'copyright_text' => 'A Brand New Org. All rights reserved.',
            'site_tagline' => 'A brand new meta description.',
            'membership_intro' => 'A brand new membership pitch.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = app(BrandingSettings::class);
    expect($settings->brand_short_name)->toBe('NEWORG');
    expect($settings->color_brand)->toBe('#1234ab');
    expect($settings->hero_headline)->toBe('A brand new headline');
    expect($settings->copyright_text)->toBe('A Brand New Org. All rights reserved.');
    expect($settings->membership_intro)->toBe('A brand new membership pitch.');
});
