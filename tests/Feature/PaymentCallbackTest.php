<?php

use App\Enums\MembershipStatus;
use App\Enums\PaymentStatus;
use App\Models\MembershipTier;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\UserMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('activates the membership toward review, invites, and auto-logs-in a new user on successful callback', function () {
    Notification::fake();

    Http::fake([
        '*/transaction/verify/*' => Http::response([
            'status' => true,
            'data' => ['status' => 'success', 'reference' => 'ICEN-CALLBACKTEST', 'channel' => 'card'],
        ]),
    ]);

    $tier = MembershipTier::factory()->create(['registration_fee' => 35000]);
    $gateway = PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    $membership = UserMembership::create([
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::PendingPayment,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'newmember@example.com',
        'phone' => '08012345678',
    ]);

    $payment = Payment::create([
        'payment_gateway_id' => $gateway->id,
        'payable_id' => $membership->id,
        'payable_type' => UserMembership::class,
        'amount' => 35000,
        'currency' => 'NGN',
        'reference' => 'ICEN-CALLBACKTEST',
    ]);

    $this->get('/payments/callback?reference=ICEN-CALLBACKTEST')
        ->assertRedirectToRoute('member.dashboard');

    expect($payment->fresh()->status)->toBe(PaymentStatus::Successful);
    expect($membership->fresh()->status)->toBe(MembershipStatus::PendingReview);

    $user = \App\Models\User::where('email', 'newmember@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->email_verified_at)->not->toBeNull();

    $this->assertAuthenticatedAs($user);

    Notification::assertSentTo($user, \App\Notifications\SetPasswordInvite::class);
});

it('sends an already-existing member matched by email to the plain success page instead of auto-logging them in', function () {
    Notification::fake();

    Http::fake([
        '*/transaction/verify/*' => Http::response([
            'status' => true,
            'data' => ['status' => 'success', 'reference' => 'ICEN-EXISTINGMATCH', 'channel' => 'card'],
        ]),
    ]);

    $existingUser = \App\Models\User::factory()->create(['email' => 'already@example.com']);

    $tier = MembershipTier::factory()->create(['registration_fee' => 35000]);
    $gateway = PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    $membership = UserMembership::create([
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::PendingPayment,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'already@example.com',
    ]);

    $payment = Payment::create([
        'payment_gateway_id' => $gateway->id,
        'payable_id' => $membership->id,
        'payable_type' => UserMembership::class,
        'amount' => 35000,
        'currency' => 'NGN',
        'reference' => 'ICEN-EXISTINGMATCH',
    ]);

    $this->get('/payments/callback?reference=ICEN-EXISTINGMATCH')
        ->assertRedirect(route('payments.success', ['payment' => $payment->id]));

    $this->assertGuest();
    expect($membership->fresh()->user_id)->toBe($existingUser->id);
});

it('leaves the payment pending when paystack reports a failed transaction', function () {
    Http::fake([
        '*/transaction/verify/*' => Http::response([
            'status' => true,
            'data' => ['status' => 'failed', 'reference' => 'ICEN-FAILTEST'],
        ]),
    ]);

    $tier = MembershipTier::factory()->create();
    $gateway = PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    $membership = UserMembership::create([
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::PendingPayment,
        'email' => 'failed@example.com',
    ]);

    $payment = Payment::create([
        'payment_gateway_id' => $gateway->id,
        'payable_id' => $membership->id,
        'payable_type' => UserMembership::class,
        'amount' => 15000,
        'currency' => 'NGN',
        'reference' => 'ICEN-FAILTEST',
    ]);

    $this->get('/payments/callback?reference=ICEN-FAILTEST')->assertRedirect();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Pending);
    expect($membership->fresh()->status)->toBe(MembershipStatus::PendingPayment);
});
