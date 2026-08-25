<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('only lets a super-admin manage users, not admins or registrars', function () {
    Role::findOrCreate('super-admin');
    Role::findOrCreate('admin');
    Role::findOrCreate('registrar');

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $registrar = User::factory()->create();
    $registrar->assignRole('registrar');

    $this->actingAs($superAdmin)->get('/admin/users')->assertOk();
    $this->actingAs($admin)->get('/admin/users')->assertForbidden();
    $this->actingAs($registrar)->get('/admin/users')->assertForbidden();
});

it('lets a super-admin create a staff account with a role and sends a set-password invite', function () {
    Notification::fake();

    $registrarRole = Role::findOrCreate('registrar');
    Role::findOrCreate('super-admin');

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    Livewire::actingAs($superAdmin)
        ->test(CreateUser::class)
        ->fillForm([
            'name' => 'New Registrar',
            'email' => 'registrar@icen.test',
            'phone' => '08011112222',
            'roles' => [$registrarRole->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $created = User::where('email', 'registrar@icen.test')->first();

    expect($created)->not->toBeNull()
        ->and($created->hasRole('registrar'))->toBeTrue();

    Notification::assertSentTo($created, \App\Notifications\SetPasswordInvite::class);
});

it('lets a super-admin change an existing user\'s roles', function () {
    Role::findOrCreate('super-admin');
    $adminRole = Role::findOrCreate('admin');

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    $member = User::factory()->create();

    Livewire::actingAs($superAdmin)
        ->test(\App\Filament\Resources\Users\Pages\EditUser::class, ['record' => $member->getRouteKey()])
        ->fillForm(['roles' => [$adminRole->id]])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($member->fresh()->hasRole('admin'))->toBeTrue();
});
