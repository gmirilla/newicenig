<?php

use App\Enums\MembershipStatus;
use App\Models\MembershipTier;
use App\Models\User;
use App\Models\UserMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('sends a staff-only account (no membership of their own) straight to the admin panel on login', function () {
    Role::findOrCreate('super-admin');

    $staff = User::factory()->create();
    $staff->assignRole('super-admin');

    Volt::test('pages.auth.login')
        ->set('form.email', $staff->email)
        ->set('form.password', 'password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect('/admin');

    $this->assertAuthenticated();
});

it('sends a plain member to the member dashboard on login, even though they could reach /admin routes if they had a role', function () {
    $member = User::factory()->create();

    expect($member->defaultDashboardUrl())->toBe(route('member.dashboard', absolute: false));
});

it('sends a staff member who also holds an active membership to the member dashboard, not /admin', function () {
    Role::findOrCreate('registrar');

    $tier = MembershipTier::factory()->create();
    $staffMember = User::factory()->create();
    $staffMember->assignRole('registrar');

    UserMembership::create([
        'user_id' => $staffMember->id,
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Active,
        'expires_at' => now()->addYear(),
    ]);

    expect($staffMember->defaultDashboardUrl())->toBe(route('member.dashboard', absolute: false));
});
