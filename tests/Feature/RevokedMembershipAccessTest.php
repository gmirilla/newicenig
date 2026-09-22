<?php

use App\Enums\MembershipStatus;
use App\Models\MemberDocument;
use App\Models\MembershipTier;
use App\Models\User;
use App\Models\UserMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function memberWithStatus(MembershipStatus $status): User
{
    $user = User::factory()->create();

    UserMembership::create([
        'user_id' => $user->id,
        'membership_tier_id' => MembershipTier::factory()->create()->id,
        'status' => $status,
        'expires_at' => $status === MembershipStatus::Active ? now()->addYear() : now()->subDay(),
    ]);

    return $user;
}

it('blocks a revoked member from the notice board and documents', function () {
    $user = memberWithStatus(MembershipStatus::Revoked);

    $this->actingAs($user)->get(route('member.notices'))->assertForbidden();
    $this->actingAs($user)->get(route('member.documents'))->assertForbidden();
});

it('blocks a revoked member from downloading their own document', function () {
    $user = memberWithStatus(MembershipStatus::Revoked);

    $document = MemberDocument::create([
        'user_id' => $user->id,
        'title' => 'Certificate scan',
        'type' => 'certificate',
    ]);

    $this->actingAs($user)
        ->get(route('member.documents.download', $document))
        ->assertForbidden();
});

it('still lets an expired member browse the notice board and documents', function () {
    $user = memberWithStatus(MembershipStatus::Expired);

    $this->actingAs($user)->get(route('member.notices'))->assertOk();
    $this->actingAs($user)->get(route('member.documents'))->assertOk();
});

it('still lets an active member browse the notice board and documents', function () {
    $user = memberWithStatus(MembershipStatus::Active);

    $this->actingAs($user)->get(route('member.notices'))->assertOk();
    $this->actingAs($user)->get(route('member.documents'))->assertOk();
});

it('does not block dashboard access for a revoked member and shows the revoked message', function () {
    $user = memberWithStatus(MembershipStatus::Revoked);

    $this->actingAs($user)
        ->get(route('member.dashboard'))
        ->assertOk()
        ->assertSee('has been revoked');
});
