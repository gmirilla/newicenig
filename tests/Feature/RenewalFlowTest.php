<?php

use App\Enums\MembershipStatus;
use App\Enums\PaymentStatus;
use App\Models\MembershipRenewal;
use App\Models\MembershipTier;
use App\Models\User;
use App\Models\UserMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('lets an active member start a renewal and redirects to paystack', function () {
    Http::fake([
        '*/transaction/initialize' => Http::response([
            'status' => true,
            'data' => ['authorization_url' => 'https://checkout.paystack.com/renew123'],
        ]),
    ]);

    $tier = MembershipTier::factory()->create(['renewal_fee' => 15000]);
    $user = User::factory()->create();

    $membership = UserMembership::create([
        'user_id' => $user->id,
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Active,
        'membership_number' => 'ICEN/2026/00001',
        'expires_at' => now()->addMonth(),
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Portal\RenewalFlow::class)
        ->call('renew')
        ->assertRedirect('https://checkout.paystack.com/renew123');

    $renewal = MembershipRenewal::first();
    expect($renewal)->not->toBeNull()
        ->and($renewal->user_membership_id)->toBe($membership->id)
        ->and($renewal->payment->status)->toBe(PaymentStatus::Pending);
});

it('extends membership expiry and marks it active after a successful renewal payment', function () {
    Http::fake([
        '*/transaction/verify/*' => Http::response([
            'status' => true,
            'data' => ['status' => 'success', 'reference' => 'ICEN-RENEWTEST', 'channel' => 'card'],
        ]),
    ]);

    $tier = MembershipTier::factory()->create(['renewal_fee' => 15000]);
    $user = User::factory()->create();
    $oldExpiry = now()->subDays(5);

    $membership = UserMembership::create([
        'user_id' => $user->id,
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Expired,
        'expires_at' => $oldExpiry,
    ]);

    $renewal = MembershipRenewal::create(['user_membership_id' => $membership->id]);

    $gateway = \App\Models\PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    $payment = \App\Models\Payment::create([
        'user_id' => $user->id,
        'payment_gateway_id' => $gateway->id,
        'payable_id' => $renewal->id,
        'payable_type' => MembershipRenewal::class,
        'amount' => 15000,
        'currency' => 'NGN',
        'reference' => 'ICEN-RENEWTEST',
    ]);

    $renewal->update(['payment_id' => $payment->id]);

    $this->get('/payments/callback?reference=ICEN-RENEWTEST')->assertRedirect();

    $membership->refresh();
    expect($membership->status)->toBe(MembershipStatus::Active)
        ->and($membership->expires_at->isFuture())->toBeTrue()
        ->and($membership->expires_at->diffInDays(now()->addYear()))->toBeLessThan(1);

    expect($renewal->fresh()->new_expires_at)->not->toBeNull();
});
