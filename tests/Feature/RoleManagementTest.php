<?php

use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('only lets a super-admin manage roles', function () {
    Role::findOrCreate('super-admin');
    Role::findOrCreate('admin');

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($superAdmin)->get('/admin/roles')->assertOk();
    $this->actingAs($admin)->get('/admin/roles')->assertForbidden();
});

it('lets a super-admin create a brand new role', function () {
    Role::findOrCreate('super-admin');
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    Livewire::actingAs($superAdmin)
        ->test(CreateRole::class)
        ->fillForm(['name' => 'finance-officer'])
        ->call('create')
        ->assertHasNoFormErrors();

    $role = Role::where('name', 'finance-officer')->first();
    expect($role)->not->toBeNull()
        ->and($role->guard_name)->toBe('web');
});

it('blocks renaming a role that gates admin panel access, even via a direct model save', function () {
    Role::findOrCreate('super-admin');
    $registrarRole = Role::findOrCreate('registrar');

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    // The "name" field is disabled in the UI for protected roles, which Filament
    // excludes from the saved payload entirely — so the rename attempt is silently
    // a no-op here, not an error. The real security boundary (in case some other
    // request path skipped the disabled field) is the direct model-save guard below.
    Livewire::actingAs($superAdmin)
        ->test(EditRole::class, ['record' => $registrarRole->getRouteKey()])
        ->fillForm(['name' => 'renamed-role'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($registrarRole->fresh()->name)->toBe('registrar');

    $registrarRole->name = 'still-renamed';
    expect(fn () => $registrarRole->save())->toThrow(RuntimeException::class);
});

it('blocks deleting a role that gates admin panel access, even via a direct model call', function () {
    $registrarRole = Role::findOrCreate('registrar');

    expect(fn () => $registrarRole->delete())->toThrow(RuntimeException::class);
    expect(Role::where('name', 'registrar')->exists())->toBeTrue();
});

it('allows renaming and deleting a custom, non-protected role', function () {
    $customRole = Role::findOrCreate('finance-officer');

    $customRole->name = 'finance-lead';
    $customRole->save();
    expect($customRole->fresh()->name)->toBe('finance-lead');

    $customRole->delete();
    expect(Role::where('name', 'finance-lead')->exists())->toBeFalse();
});
