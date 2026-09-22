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

it('bulk-approves only the selected memberships that are pending review', function () {
    Notification::fake();

    $admin = actingSuperAdmin();
    $tier = MembershipTier::factory()->create();

    $pendingA = UserMembership::create([
        'user_id' => User::factory()->create()->id,
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::PendingReview,
    ]);
    $pendingB = UserMembership::create([
        'user_id' => User::factory()->create()->id,
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::PendingReview,
    ]);
    $alreadyActive = UserMembership::create([
        'user_id' => User::factory()->create()->id,
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Active,
        'expires_at' => now()->addMonth(),
    ]);

    Livewire::actingAs($admin)
        ->test(ListUserMemberships::class)
        ->callTableBulkAction('bulkApprove', [$pendingA, $pendingB, $alreadyActive]);

    expect($pendingA->fresh()->status)->toBe(MembershipStatus::Active)
        ->and($pendingA->fresh()->membership_number)->not->toBeNull()
        ->and($pendingB->fresh()->status)->toBe(MembershipStatus::Active)
        ->and($alreadyActive->fresh()->expires_at->isSameDay(now()->addMonth()))->toBeTrue();

    Notification::assertSentTo($pendingA->user, \App\Notifications\MembershipApproved::class);
    Notification::assertSentTo($pendingB->user, \App\Notifications\MembershipApproved::class);
    Notification::assertNotSentTo($alreadyActive->user, \App\Notifications\MembershipApproved::class);
});

it('bulk-revokes only the selected memberships that are not already revoked, with one shared reason', function () {
    Notification::fake();

    $admin = actingSuperAdmin();
    $tier = MembershipTier::factory()->create();

    $activeA = UserMembership::create([
        'user_id' => User::factory()->create()->id,
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Active,
        'expires_at' => now()->addMonth(),
    ]);
    $activeB = UserMembership::create([
        'user_id' => User::factory()->create()->id,
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Active,
        'expires_at' => now()->addMonth(),
    ]);
    $alreadyRevoked = UserMembership::create([
        'user_id' => User::factory()->create()->id,
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Revoked,
        'revoked_at' => now()->subWeek(),
    ]);

    Livewire::actingAs($admin)
        ->test(ListUserMemberships::class)
        ->callTableBulkAction('bulkRevoke', [$activeA, $activeB, $alreadyRevoked], data: ['reason' => 'Mass policy violation.']);

    expect($activeA->fresh()->status)->toBe(MembershipStatus::Revoked)
        ->and($activeA->fresh()->revoke_reason)->toBe('Mass policy violation.')
        ->and($activeA->fresh()->revoked_by)->toBe($admin->id)
        ->and($activeB->fresh()->status)->toBe(MembershipStatus::Revoked);

    Notification::assertSentTo($activeA->user, MembershipRevoked::class);
    Notification::assertSentTo($activeB->user, MembershipRevoked::class);
    Notification::assertNotSentTo($alreadyRevoked->user, MembershipRevoked::class);
});

it('bulk-reinstates only the selected memberships that are currently revoked, restoring each per its own expiry', function () {
    Notification::fake();

    $admin = actingSuperAdmin();
    $tier = MembershipTier::factory()->create();

    $revokedStillValid = UserMembership::create([
        'user_id' => User::factory()->create()->id,
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Revoked,
        'revoked_at' => now()->subWeek(),
        'expires_at' => now()->addMonth(),
    ]);
    $revokedLapsed = UserMembership::create([
        'user_id' => User::factory()->create()->id,
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Revoked,
        'revoked_at' => now()->subMonth(),
        'expires_at' => now()->subWeek(),
    ]);
    $alreadyActive = UserMembership::create([
        'user_id' => User::factory()->create()->id,
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Active,
        'expires_at' => now()->addMonth(),
    ]);

    Livewire::actingAs($admin)
        ->test(ListUserMemberships::class)
        ->callTableBulkAction('bulkReinstate', [$revokedStillValid, $revokedLapsed, $alreadyActive]);

    expect($revokedStillValid->fresh()->status)->toBe(MembershipStatus::Active)
        ->and($revokedLapsed->fresh()->status)->toBe(MembershipStatus::Expired);

    Notification::assertSentTo($revokedStillValid->user, MembershipReinstated::class);
    Notification::assertSentTo($revokedLapsed->user, MembershipReinstated::class);
    Notification::assertNotSentTo($alreadyActive->user, MembershipReinstated::class);
});
