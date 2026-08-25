<?php

use App\Enums\MembershipStatus;
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
