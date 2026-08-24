<?php

use App\Filament\Pages\ManageGeneralSettings;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('lets a super-admin view every CMS resource index and create page', function () {
    Role::findOrCreate('super-admin');

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $resources = [
        'pages', 'posts', 'events', 'team-members', 'testimonials', 'downloads',
        'membership-tiers', 'user-memberships', 'payments', 'payment-gateways',
    ];

    foreach ($resources as $resource) {
        $this->actingAs($admin)->get("/admin/{$resource}")->assertOk();
        $this->actingAs($admin)->get("/admin/{$resource}/create")->assertOk();
    }
});

it('lets a super-admin view and save the general settings page', function () {
    Role::findOrCreate('super-admin');

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin)->get('/admin/manage-general-settings')->assertOk();

    Livewire::actingAs($admin)
        ->test(ManageGeneralSettings::class)
        ->fillForm(['org_name' => 'ICEN Test Org'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(app(GeneralSettings::class)->org_name)->toBe('ICEN Test Org');
});
