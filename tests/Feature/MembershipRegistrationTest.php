<?php

use App\Enums\MembershipStatus;
use App\Enums\PaymentStatus;
use App\Models\MembershipTier;
use App\Models\Payment;
use App\Models\UserMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('walks through the registration wizard and redirects to paystack', function () {
    Http::fake([
        '*/transaction/initialize' => Http::response([
            'status' => true,
            'data' => [
                'authorization_url' => 'https://checkout.paystack.com/abc123',
                'reference' => 'ICEN-TESTREF',
            ],
        ]),
    ]);

    $tier = MembershipTier::factory()->create([
        'registration_fee' => 35000,
        'requires_employer_info' => false,
        'requires_qualification_upload' => false,
    ]);

    Livewire::test(\App\Livewire\Portal\MembershipRegistrationWizard::class)
        ->call('selectTier', $tier->id)
        ->assertSet('step', 2)
        ->set('first_name', 'Jane')
        ->set('last_name', 'Doe')
        ->set('email', 'jane@example.com')
        ->set('phone', '08012345678')
        ->call('continueToReview')
        ->assertSet('step', 3)
        ->call('submitAndPay')
        ->assertRedirect('https://checkout.paystack.com/abc123');

    $membership = UserMembership::first();
    expect($membership)->not->toBeNull()
        ->and($membership->status)->toBe(MembershipStatus::PendingPayment)
        ->and($membership->email)->toBe('jane@example.com');

    $payment = Payment::first();
    expect($payment)->not->toBeNull()
        ->and($payment->status)->toBe(PaymentStatus::Pending)
        ->and((float) $payment->amount)->toBe(35000.0);

    Http::assertSent(fn ($request) => $request->url() === 'https://api.paystack.co/transaction/initialize'
        && $request['email'] === 'jane@example.com'
        && $request['amount'] === 3500000);
});

it('requires employer info and validates before moving past the details step', function () {
    $tier = MembershipTier::factory()->create(['requires_employer_info' => true]);

    Livewire::test(\App\Livewire\Portal\MembershipRegistrationWizard::class)
        ->call('selectTier', $tier->id)
        ->set('first_name', 'Jane')
        ->set('last_name', 'Doe')
        ->set('email', 'jane@example.com')
        ->set('phone', '08012345678')
        ->call('continueToReview')
        ->assertHasErrors(['employer_name', 'job_title'])
        ->assertSet('step', 2);
});
