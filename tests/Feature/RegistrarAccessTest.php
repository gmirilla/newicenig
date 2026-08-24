<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('lets a registrar manage memberships and payments but not site content or settings', function () {
    Role::findOrCreate('registrar');

    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    $this->actingAs($registrar)->get('/admin/user-memberships')->assertOk();
    $this->actingAs($registrar)->get('/admin/payments')->assertOk();

    $this->actingAs($registrar)->get('/admin/pages')->assertForbidden();
    $this->actingAs($registrar)->get('/admin/posts')->assertForbidden();
    $this->actingAs($registrar)->get('/admin/membership-tiers')->assertForbidden();
    $this->actingAs($registrar)->get('/admin/payment-gateways')->assertForbidden();
    $this->actingAs($registrar)->get('/admin/manage-general-settings')->assertForbidden();
});
