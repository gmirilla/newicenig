<?php

use App\Enums\MembershipStatus;
use App\Models\MembershipTier;
use App\Models\User;
use App\Models\UserMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets an active member download their certificate as a pdf', function () {
    $tier = MembershipTier::factory()->create();
    $user = User::factory()->create();

    UserMembership::create([
        'user_id' => $user->id,
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Active,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'membership_number' => 'ICEN/2026/00001',
        'verified_at' => now(),
        'expires_at' => now()->addYear(),
    ]);

    $response = $this->actingAs($user)->get('/member/certificate');

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('blocks certificate download for a member without an active membership', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/member/certificate')->assertForbidden();
});
