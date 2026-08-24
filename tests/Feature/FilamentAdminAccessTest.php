<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('lets a super-admin view the admin dashboard with its stats widget', function () {
    Role::findOrCreate('super-admin');

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin)->get('/admin')->assertOk();
});

it('blocks a plain member from accessing the admin panel', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

it('blocks a guest from accessing the admin panel', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});
