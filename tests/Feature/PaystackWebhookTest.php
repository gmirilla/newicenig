<?php

use App\Enums\MembershipStatus;
use App\Enums\PaymentStatus;
use App\Models\MembershipTier;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\UserMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function makeMembershipPayment(): Payment
{
    $tier = MembershipTier::factory()->create(['registration_fee' => 35000]);
    $gateway = PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    $membership = UserMembership::create([
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::PendingPayment,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
        'phone' => '08012345678',
    ]);

    return Payment::create([
        'payment_gateway_id' => $gateway->id,
        'payable_id' => $membership->id,
        'payable_type' => UserMembership::class,
        'amount' => 35000,
        'currency' => 'NGN',
        'reference' => 'ICEN-WEBHOOKTEST',
    ]);
}

it('rejects a webhook with a missing or invalid signature', function () {
    $payment = makeMembershipPayment();

    $this->postJson('/payments/webhook', [
        'event' => 'charge.success',
        'data' => ['reference' => $payment->reference],
    ])->assertStatus(400);

    $this->postJson('/payments/webhook', [
        'event' => 'charge.success',
        'data' => ['reference' => $payment->reference],
    ], ['x-paystack-signature' => 'not-a-real-signature'])
        ->assertStatus(400);

    expect($payment->fresh()->status)->toBe(PaymentStatus::Pending);
});

it('accepts a webhook with a valid signature and activates the membership toward review', function () {
    tap(app(\App\Settings\PaystackSettings::class), function ($settings) {
        $settings->secret_key = 'test-secret';
        $settings->save();
    });

    Http::fake([
        '*/transaction/verify/*' => Http::response([
            'status' => true,
            'data' => [
                'status' => 'success',
                'reference' => 'ICEN-WEBHOOKTEST',
                'channel' => 'card',
            ],
        ]),
    ]);

    $payment = makeMembershipPayment();

    $body = json_encode([
        'event' => 'charge.success',
        'data' => ['reference' => $payment->reference],
    ]);

    $signature = hash_hmac('sha512', $body, 'test-secret');

    $this->call('POST', '/payments/webhook', [], [], [], [
        'HTTP_x-paystack-signature' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $body)->assertStatus(200);

    $payment->refresh();
    expect($payment->status)->toBe(PaymentStatus::Successful);

    $membership = $payment->payable()->first();
    expect($membership->status)->toBe(MembershipStatus::PendingReview);
});
