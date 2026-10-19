<?php

use App\Actions\Payments\CheckPendingPaystackPayment;
use App\Enums\MembershipStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Jobs\VerifyPaystackPayment;
use App\Mail\PaystackPaymentUnresolved;
use App\Models\MembershipTier;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\UserMembership;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function pendingPaystackPayment(string $reference, array $attempt = []): Payment
{
    $tier = MembershipTier::factory()->create(['registration_fee' => 35000]);
    $gateway = PaymentGateway::firstOrCreate(['slug' => 'paystack'], ['name' => 'Paystack', 'is_active' => true]);

    $membership = UserMembership::create([
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::PendingPayment,
        'email' => 'pending@example.com',
    ]);

    return Payment::create(array_merge([
        'payment_gateway_id' => $gateway->id,
        'payable_id' => $membership->id,
        'payable_type' => UserMembership::class,
        'amount' => 35000,
        'currency' => 'NGN',
        'reference' => $reference,
        'status' => PaymentStatus::Pending,
        'payment_method' => PaymentMethod::Paystack,
    ], $attempt));
}

it('resolves the payment and clears retry state when paystack confirms success', function () {
    Http::fake(['*/transaction/verify/*' => Http::response([
        'status' => true,
        'data' => ['status' => 'success', 'reference' => 'ICEN-RESOLVE-SUCCESS', 'channel' => 'card'],
    ])]);

    $payment = pendingPaystackPayment('ICEN-RESOLVE-SUCCESS', ['paystack_check_attempts' => 2, 'next_paystack_check_at' => now()]);

    $result = app(CheckPendingPaystackPayment::class)->handle($payment);

    expect($result)->toBe('resolved');
    expect($payment->fresh()->status)->toBe(PaymentStatus::Successful);
    expect($payment->fresh()->next_paystack_check_at)->toBeNull();
});

it('resolves the payment as abandoned and stops retrying', function () {
    Http::fake(['*/transaction/verify/*' => Http::response([
        'status' => true,
        'data' => ['status' => 'abandoned', 'reference' => 'ICEN-ABANDONED'],
    ])]);

    $payment = pendingPaystackPayment('ICEN-ABANDONED');

    $result = app(CheckPendingPaystackPayment::class)->handle($payment);

    expect($result)->toBe('resolved');
    expect($payment->fresh()->status)->toBe(PaymentStatus::Abandoned);
    expect($payment->fresh()->next_paystack_check_at)->toBeNull();
});

it('reschedules another check and queues the retry job when paystack is still undecided', function () {
    Queue::fake();

    Http::fake(['*/transaction/verify/*' => Http::response([
        'status' => true,
        'data' => ['status' => 'pending', 'reference' => 'ICEN-RETRY-2'],
    ])]);

    $payment = pendingPaystackPayment('ICEN-RETRY-2', ['paystack_check_attempts' => 1]);

    $result = app(CheckPendingPaystackPayment::class)->handle($payment);

    expect($result)->toBe('still_pending');
    $payment->refresh();
    expect($payment->paystack_check_attempts)->toBe(2);
    expect($payment->next_paystack_check_at)->not->toBeNull();

    Queue::assertPushed(VerifyPaystackPayment::class, fn ($job) => $job->paymentId === $payment->id);
});

it('gives up after the 5th check and alerts the configured mailing list exactly once', function () {
    Queue::fake();
    Mail::fake();

    app(GeneralSettings::class)->payment_notification_recipients = ['registrar@icen.test'];
    app(GeneralSettings::class)->save();

    Http::fake(['*/transaction/verify/*' => Http::response([
        'status' => true,
        'data' => ['status' => 'pending', 'reference' => 'ICEN-EXHAUST'],
    ])]);

    // Already failed 4 times — this is the 5th.
    $payment = pendingPaystackPayment('ICEN-EXHAUST', ['paystack_check_attempts' => 4]);

    $result = app(CheckPendingPaystackPayment::class)->handle($payment);

    expect($result)->toBe('exhausted');
    $payment->refresh();
    expect($payment->paystack_check_attempts)->toBe(5);
    expect($payment->next_paystack_check_at)->toBeNull();
    expect($payment->status)->toBe(PaymentStatus::Pending);

    Queue::assertNotPushed(VerifyPaystackPayment::class);
    Mail::assertQueued(PaystackPaymentUnresolved::class, fn ($mail) => $mail->hasTo('registrar@icen.test') && $mail->payment->is($payment));
});

it('does not alert staff when no recipients are configured', function () {
    Mail::fake();

    Http::fake(['*/transaction/verify/*' => Http::response([
        'status' => true,
        'data' => ['status' => 'pending', 'reference' => 'ICEN-EXHAUST-NORECIPIENTS'],
    ])]);

    $payment = pendingPaystackPayment('ICEN-EXHAUST-NORECIPIENTS', ['paystack_check_attempts' => 4]);

    app(CheckPendingPaystackPayment::class)->handle($payment);

    Mail::assertNothingQueued();
});

it('skips a payment that has already resolved, without calling paystack again', function () {
    Http::fake(['*/transaction/verify/*' => Http::response(['status' => true, 'data' => []])]);

    $payment = pendingPaystackPayment('ICEN-ALREADY-RESOLVED', ['status' => PaymentStatus::Successful]);

    $result = app(CheckPendingPaystackPayment::class)->handle($payment);

    expect($result)->toBe('skipped');
    Http::assertNothingSent();
});

it('skips a bank-transfer payment even if it is pending', function () {
    Http::fake(['*/transaction/verify/*' => Http::response(['status' => true, 'data' => []])]);

    $payment = pendingPaystackPayment('ICEN-BANKTRANSFER', ['payment_method' => PaymentMethod::BankTransfer]);

    $result = app(CheckPendingPaystackPayment::class)->handle($payment);

    expect($result)->toBe('skipped');
    Http::assertNothingSent();
});

it('resumes the full retry chain when manually rechecked after being exhausted', function () {
    Queue::fake();

    Http::fake(['*/transaction/verify/*' => Http::response([
        'status' => true,
        'data' => ['status' => 'pending', 'reference' => 'ICEN-MANUAL-RESUME'],
    ])]);

    $payment = pendingPaystackPayment('ICEN-MANUAL-RESUME', ['paystack_check_attempts' => 5, 'next_paystack_check_at' => null]);

    $result = app(CheckPendingPaystackPayment::class)->recheckManually($payment);

    expect($result)->toBe('still_pending');
    $payment->refresh();
    expect($payment->paystack_check_attempts)->toBe(1);
    expect($payment->next_paystack_check_at)->not->toBeNull();

    Queue::assertPushed(VerifyPaystackPayment::class);
});

it('the queued job finds the payment and re-dispatches itself when still pending', function () {
    Queue::fake();

    Http::fake(['*/transaction/verify/*' => Http::response([
        'status' => true,
        'data' => ['status' => 'pending', 'reference' => 'ICEN-JOB-CHAIN'],
    ])]);

    $payment = pendingPaystackPayment('ICEN-JOB-CHAIN', ['paystack_check_attempts' => 2]);

    (new VerifyPaystackPayment($payment->id))->handle(app(CheckPendingPaystackPayment::class));

    expect($payment->fresh()->paystack_check_attempts)->toBe(3);
    Queue::assertPushed(VerifyPaystackPayment::class, fn ($job) => $job->paymentId === $payment->id);
});

it('the sweep command resolves overdue pending payments without the queue', function () {
    Http::fake(['*/transaction/verify/*' => Http::response([
        'status' => true,
        'data' => ['status' => 'success', 'reference' => 'ICEN-SWEEP-DUE', 'channel' => 'card'],
    ])]);

    $due = pendingPaystackPayment('ICEN-SWEEP-DUE', ['paystack_check_attempts' => 3, 'next_paystack_check_at' => now()->subMinute()]);
    $notYetDue = pendingPaystackPayment('ICEN-SWEEP-NOTDUE', ['paystack_check_attempts' => 1, 'next_paystack_check_at' => now()->addMinutes(5)]);

    $this->artisan('app:recheck-pending-paystack-payments')->assertSuccessful();

    expect($due->fresh()->status)->toBe(PaymentStatus::Successful);
    expect($notYetDue->fresh()->status)->toBe(PaymentStatus::Pending);
});
