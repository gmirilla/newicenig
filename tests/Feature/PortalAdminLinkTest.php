<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('shows the Admin Dashboard link in the member portal nav to staff roles', function () {
    Role::findOrCreate('registrar');

    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    $this->actingAs($registrar)
        ->get('/member/dashboard')
        ->assertOk()
        ->assertSee('Admin Dashboard')
        ->assertSee('/admin', escape: false);
});

it('hides the Admin Dashboard link from a plain member', function () {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->get('/member/dashboard')
        ->assertOk()
        ->assertDontSee('Admin Dashboard');
});
