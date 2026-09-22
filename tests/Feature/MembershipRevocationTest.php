<?php

use App\Enums\MembershipStatus;
use App\Filament\Resources\UserMemberships\Pages\ListUserMemberships;
use App\Models\MembershipTier;
use App\Models\User;
use App\Models\UserMembership;
use App\Notifications\MembershipReinstated;
use App\Notifications\MembershipRevoked;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function actingSuperAdmin(): User
{
    Role::findOrCreate('super-admin');

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    return $admin;
}

it('revokes a membership with a reason, stamps who and when, and notifies the member', function () {
    Notification::fake();

    $admin = actingSuperAdmin();
    $member = User::factory()->create();

    $membership = UserMembership::create([
        'user_id' => $member->id,
        'membership_tier_id' => MembershipTier::factory()->create()->id,
        'status' => MembershipStatus::Active,
        'expires_at' => now()->addMonth(),
    ]);

    Livewire::actingAs($admin)
        ->test(ListUserMemberships::class)
        ->callTableAction('revoke', $membership, data: ['reason' => 'Violated the code of ethics.']);

    $membership->refresh();
    expect($membership->status)->toBe(MembershipStatus::Revoked)
        ->and($membership->revoked_by)->toBe($admin->id)
        ->and($membership->revoke_reason)->toBe('Violated the code of ethics.')
        ->and($membership->revoked_at)->not->toBeNull();

    Notification::assertSentTo($member, MembershipRevoked::class);
});

it('reinstates a revoked membership to active when its term has not lapsed, and notifies the member', function () {
    Notification::fake();

    $admin = actingSuperAdmin();
    $member = User::factory()->create();

    $membership = UserMembership::create([
        'user_id' => $member->id,
        'membership_tier_id' => MembershipTier::factory()->create()->id,
        'status' => MembershipStatus::Revoked,
        'revoked_at' => now()->subDay(),
        'revoked_by' => $admin->id,
        'revoke_reason' => 'Under investigation.',
        'expires_at' => now()->addMonth(),
    ]);

    Livewire::actingAs($admin)
        ->test(ListUserMemberships::class)
        ->callTableAction('reinstate', $membership);

    expect($membership->fresh()->status)->toBe(MembershipStatus::Active);

    Notification::assertSentTo($member, MembershipReinstated::class);
});

it('reinstates a revoked membership to expired instead of active when its term has already lapsed', function () {
    Notification::fake();

    $admin = actingSuperAdmin();
    $member = User::factory()->create();

    $membership = UserMembership::create([
        'user_id' => $member->id,
        'membership_tier_id' => MembershipTier::factory()->create()->id,
        'status' => MembershipStatus::Revoked,
        'revoked_at' => now()->subMonth(),
        'revoked_by' => $admin->id,
        'expires_at' => now()->subWeek(),
    ]);

    Livewire::actingAs($admin)
        ->test(ListUserMemberships::class)
        ->callTableAction('reinstate', $membership);

    expect($membership->fresh()->status)->toBe(MembershipStatus::Expired);
});

it('requires a reason before a revocation can be submitted', function () {
    $admin = actingSuperAdmin();

    $membership = UserMembership::create([
        'membership_tier_id' => MembershipTier::factory()->create()->id,
        'status' => MembershipStatus::Active,
        'expires_at' => now()->addMonth(),
    ]);

    Livewire::actingAs($admin)
        ->test(ListUserMemberships::class)
        ->callTableAction('revoke', $membership, data: ['reason' => ''])
        ->assertHasTableActionErrors(['reason' => 'required']);

    expect($membership->fresh()->status)->toBe(MembershipStatus::Active);
});
