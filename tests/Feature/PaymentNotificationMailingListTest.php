<?php

use App\Actions\Payments\HandleSuccessfulPayment;
use App\Enums\MembershipStatus;
use App\Enums\PaymentStatus;
use App\Mail\MembershipPaymentNotification;
use App\Models\MembershipRenewal;
use App\Models\MembershipTier;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Models\UserMembership;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function setApprovedMailingList(array $emails): void
{
    $settings = app(GeneralSettings::class);
    $settings->payment_notification_recipients = $emails;
    $settings->save();
}

it('emails the approved mailing list with a member information pdf on a successful registration payment', function () {
    Mail::fake();
    setApprovedMailingList(['registrar@icen.test', 'finance@icen.test']);

    Http::fake([
        '*/transaction/verify/*' => Http::response([
            'status' => true,
            'data' => ['status' => 'success', 'reference' => 'ICEN-NOTIFYTEST', 'channel' => 'card'],
        ]),
    ]);

    $tier = MembershipTier::factory()->create(['registration_fee' => 35000]);
    $gateway = PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    $membership = UserMembership::create([
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::PendingPayment,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
    ]);

    $payment = Payment::create([
        'payment_gateway_id' => $gateway->id,
        'payable_id' => $membership->id,
        'payable_type' => UserMembership::class,
        'amount' => 35000,
        'currency' => 'NGN',
        'reference' => 'ICEN-NOTIFYTEST',
    ]);

    $this->get('/payments/callback?reference=ICEN-NOTIFYTEST')->assertRedirect();

    Mail::assertQueued(MembershipPaymentNotification::class, function ($mail) use ($membership) {
        return $mail->hasTo('registrar@icen.test')
            && $mail->hasTo('finance@icen.test')
            && $mail->context === 'registration'
            && $mail->membership->is($membership)
            && count($mail->attachments()) === 1;
    });
});

it('emails the approved mailing list on a successful renewal payment', function () {
    Mail::fake();
    setApprovedMailingList(['registrar@icen.test']);

    Http::fake([
        '*/transaction/verify/*' => Http::response([
            'status' => true,
            'data' => ['status' => 'success', 'reference' => 'ICEN-RENEWNOTIFY', 'channel' => 'card'],
        ]),
    ]);

    $tier = MembershipTier::factory()->create(['renewal_fee' => 15000]);
    $user = User::factory()->create();

    $membership = UserMembership::create([
        'user_id' => $user->id,
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Expired,
        'expires_at' => now()->subDays(5),
    ]);

    $renewal = MembershipRenewal::create(['user_membership_id' => $membership->id]);
    $gateway = PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    $payment = Payment::create([
        'user_id' => $user->id,
        'payment_gateway_id' => $gateway->id,
        'payable_id' => $renewal->id,
        'payable_type' => MembershipRenewal::class,
        'amount' => 15000,
        'currency' => 'NGN',
        'reference' => 'ICEN-RENEWNOTIFY',
    ]);

    $renewal->update(['payment_id' => $payment->id]);

    $this->get('/payments/callback?reference=ICEN-RENEWNOTIFY')->assertRedirect();

    Mail::assertQueued(MembershipPaymentNotification::class, function ($mail) use ($membership) {
        return $mail->hasTo('registrar@icen.test')
            && $mail->context === 'renewal'
            && $mail->membership->is($membership);
    });
});

it('does not send a notification when no approved recipients are configured', function () {
    Mail::fake();
    setApprovedMailingList([]);

    Http::fake([
        '*/transaction/verify/*' => Http::response([
            'status' => true,
            'data' => ['status' => 'success', 'reference' => 'ICEN-NORECIPIENTS', 'channel' => 'card'],
        ]),
    ]);

    $tier = MembershipTier::factory()->create(['registration_fee' => 35000]);
    $gateway = PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    $membership = UserMembership::create([
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::PendingPayment,
        'email' => 'norecipients@example.com',
    ]);

    $payment = Payment::create([
        'payment_gateway_id' => $gateway->id,
        'payable_id' => $membership->id,
        'payable_type' => UserMembership::class,
        'amount' => 35000,
        'currency' => 'NGN',
        'reference' => 'ICEN-NORECIPIENTS',
    ]);

    $this->get('/payments/callback?reference=ICEN-NORECIPIENTS')->assertRedirect();

    Mail::assertNothingQueued();
    Mail::assertNothingSent();
});

it('resends the mailing list notification for an already-successful payment without altering its state', function () {
    Mail::fake();
    setApprovedMailingList(['registrar@icen.test']);

    $tier = MembershipTier::factory()->create(['registration_fee' => 35000]);
    $gateway = PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    $membership = UserMembership::create([
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::PendingReview,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
    ]);

    $payment = Payment::create([
        'payment_gateway_id' => $gateway->id,
        'payable_id' => $membership->id,
        'payable_type' => UserMembership::class,
        'amount' => 35000,
        'currency' => 'NGN',
        'reference' => 'ICEN-RESEND',
        'status' => \App\Enums\PaymentStatus::Successful,
        'paid_at' => now(),
    ]);

    $sent = app(\App\Actions\Payments\HandleSuccessfulPayment::class)->resendApprovedMailingListNotification($payment);

    expect($sent)->toBeTrue();
    expect($payment->fresh()->status)->toBe(\App\Enums\PaymentStatus::Successful);
    expect($membership->fresh()->status)->toBe(MembershipStatus::PendingReview);

    Mail::assertQueued(MembershipPaymentNotification::class, function ($mail) use ($membership) {
        return $mail->hasTo('registrar@icen.test')
            && $mail->context === 'registration'
            && $mail->membership->is($membership);
    });
});

it('reports no email was sent when resending without any approved recipients configured', function () {
    Mail::fake();
    setApprovedMailingList([]);

    $tier = MembershipTier::factory()->create(['registration_fee' => 35000]);
    $gateway = PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    $membership = UserMembership::create([
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::PendingReview,
        'email' => 'jane@example.com',
    ]);

    $payment = Payment::create([
        'payment_gateway_id' => $gateway->id,
        'payable_id' => $membership->id,
        'payable_type' => UserMembership::class,
        'amount' => 35000,
        'currency' => 'NGN',
        'reference' => 'ICEN-RESEND-NORECIPIENTS',
        'status' => \App\Enums\PaymentStatus::Successful,
        'paid_at' => now(),
    ]);

    $sent = app(\App\Actions\Payments\HandleSuccessfulPayment::class)->resendApprovedMailingListNotification($payment);

    expect($sent)->toBeFalse();
    Mail::assertNothingQueued();
});

it('resends the mailing list notification with the renewal context for a successful renewal payment', function () {
    Mail::fake();
    setApprovedMailingList(['registrar@icen.test']);

    $tier = MembershipTier::factory()->create(['renewal_fee' => 15000]);
    $user = User::factory()->create();
    $membership = UserMembership::create([
        'user_id' => $user->id,
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Active,
        'expires_at' => now()->addYear(),
    ]);
    $renewal = MembershipRenewal::create(['user_membership_id' => $membership->id]);
    $gateway = PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    $payment = Payment::create([
        'user_id' => $user->id,
        'payment_gateway_id' => $gateway->id,
        'payable_id' => $renewal->id,
        'payable_type' => MembershipRenewal::class,
        'amount' => 15000,
        'currency' => 'NGN',
        'reference' => 'ICEN-RESEND-RENEWAL',
        'status' => PaymentStatus::Successful,
        'paid_at' => now(),
    ]);

    expect(app(HandleSuccessfulPayment::class)->resendApprovedMailingListNotification($payment))->toBeTrue();

    Mail::assertQueued(MembershipPaymentNotification::class, fn ($mail) => $mail->context === 'renewal' && $mail->membership->is($membership));
});

it('resends the mailing list notification with the level_change context when a previous membership exists', function () {
    Mail::fake();
    setApprovedMailingList(['registrar@icen.test']);

    $tier = MembershipTier::factory()->create();
    $previous = UserMembership::create([
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Active,
        'email' => 'jane@example.com',
    ]);
    $membership = UserMembership::create([
        'membership_tier_id' => MembershipTier::factory()->create()->id,
        'previous_membership_id' => $previous->id,
        'status' => MembershipStatus::PendingReview,
        'email' => 'jane@example.com',
    ]);
    $gateway = PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    $payment = Payment::create([
        'payment_gateway_id' => $gateway->id,
        'payable_id' => $membership->id,
        'payable_type' => UserMembership::class,
        'amount' => 35000,
        'currency' => 'NGN',
        'reference' => 'ICEN-RESEND-LEVEL',
        'status' => PaymentStatus::Successful,
        'paid_at' => now(),
    ]);

    expect(app(HandleSuccessfulPayment::class)->resendApprovedMailingListNotification($payment))->toBeTrue();

    Mail::assertQueued(MembershipPaymentNotification::class, fn ($mail) => $mail->context === 'level_change');
});

it('throws a clear error when resending a payment that is not linked to a membership or renewal', function () {
    Mail::fake();
    setApprovedMailingList(['registrar@icen.test']);

    $gateway = PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    $payment = Payment::create([
        'payment_gateway_id' => $gateway->id,
        'payable_id' => 999999,
        'payable_type' => UserMembership::class,
        'amount' => 35000,
        'currency' => 'NGN',
        'reference' => 'ICEN-RESEND-ORPHAN',
        'status' => PaymentStatus::Successful,
        'paid_at' => now(),
    ]);

    expect(fn () => app(HandleSuccessfulPayment::class)->resendApprovedMailingListNotification($payment))
        ->toThrow(DomainException::class, 'not linked to a membership');

    Mail::assertNothingQueued();
});

it('renders the member information pdf without errors', function () {
    $tier = MembershipTier::factory()->create();

    $membership = UserMembership::create([
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Active,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
        'phone' => '08012345678',
        'declaration_name' => 'Jane Doe',
        'declaration_accepted_at' => now(),
    ]);

    $pdf = app(\App\Services\Certificates\MemberInformationPdf::class)->build($membership);

    expect($pdf->output())->not->toBeEmpty();
});
