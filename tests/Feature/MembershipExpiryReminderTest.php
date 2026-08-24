<?php

use App\Enums\MembershipStatus;
use App\Models\MembershipTier;
use App\Models\User;
use App\Models\UserMembership;
use App\Notifications\MembershipExpiringReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('notifies members whose membership expires in exactly 30 or 7 days, and no one else', function () {
    Notification::fake();

    $tier = MembershipTier::factory()->create();

    $expiringIn30 = User::factory()->create();
    UserMembership::create([
        'user_id' => $expiringIn30->id,
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Active,
        'expires_at' => now()->addDays(30),
    ]);

    $expiringIn7 = User::factory()->create();
    UserMembership::create([
        'user_id' => $expiringIn7->id,
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Active,
        'expires_at' => now()->addDays(7),
    ]);

    $expiringIn15 = User::factory()->create();
    UserMembership::create([
        'user_id' => $expiringIn15->id,
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Active,
        'expires_at' => now()->addDays(15),
    ]);

    $this->artisan('app:send-membership-expiry-reminders')->assertSuccessful();

    Notification::assertSentTo($expiringIn30, MembershipExpiringReminder::class);
    Notification::assertSentTo($expiringIn7, MembershipExpiringReminder::class);
    Notification::assertNotSentTo($expiringIn15, MembershipExpiringReminder::class);
});
