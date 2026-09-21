<?php

use App\Enums\MembershipStatus;
use App\Enums\PaymentStatus;
use App\Models\MembershipRenewal;
use App\Models\MembershipTier;
use App\Models\User;
use App\Models\UserMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
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
        ->set('yearOfInduction', '2015')
        ->call('renew')
        ->assertRedirect('https://checkout.paystack.com/renew123');

    $renewal = MembershipRenewal::first();
    expect($renewal)->not->toBeNull()
        ->and($renewal->user_membership_id)->toBe($membership->id)
        ->and($renewal->payment->status)->toBe(PaymentStatus::Pending);

    expect($membership->fresh()->year_of_induction)->toBe('2015');
});

it('requires a year of induction before starting a renewal', function () {
    $tier = MembershipTier::factory()->create(['renewal_fee' => 15000]);
    $user = User::factory()->create();

    UserMembership::create([
        'user_id' => $user->id,
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Active,
        'expires_at' => now()->addMonth(),
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Portal\RenewalFlow::class)
        ->set('yearOfInduction', '')
        ->call('renew')
        ->assertHasErrors(['yearOfInduction' => 'required']);

    expect(MembershipRenewal::count())->toBe(0);
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

it('lets a guest with no account look up a membership by number and email and start a renewal', function () {
    Http::fake([
        '*/transaction/initialize' => Http::response([
            'status' => true,
            'data' => ['authorization_url' => 'https://checkout.paystack.com/guest-renew123'],
        ]),
    ]);

    $tier = MembershipTier::factory()->create(['renewal_fee' => 15000]);

    $membership = UserMembership::create([
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Active,
        'membership_number' => 'ICEN/2026/00099',
        'email' => 'guest@example.com',
        'expires_at' => now()->addMonth(),
    ]);

    Livewire::test(\App\Livewire\Portal\RenewalFlow::class)
        ->set('lookupMembershipNumber', 'icen/2026/00099')
        ->set('lookupEmail', 'GUEST@example.com')
        ->call('lookup')
        ->assertSet('lookupFailed', false)
        ->set('yearOfInduction', '2018')
        ->call('renew')
        ->assertRedirect('https://checkout.paystack.com/guest-renew123');

    $renewal = MembershipRenewal::first();
    expect($renewal)->not->toBeNull()
        ->and($renewal->user_membership_id)->toBe($membership->id);

    expect($membership->fresh()->year_of_induction)->toBe('2018');
});

it('gives a guest a generic error for a membership number and email that do not match', function () {
    UserMembership::create([
        'membership_tier_id' => MembershipTier::factory()->create()->id,
        'status' => MembershipStatus::Active,
        'membership_number' => 'ICEN/2026/00050',
        'email' => 'real@example.com',
        'expires_at' => now()->addMonth(),
    ]);

    Livewire::test(\App\Livewire\Portal\RenewalFlow::class)
        ->set('lookupMembershipNumber', 'ICEN/2026/00050')
        ->set('lookupEmail', 'wrong@example.com')
        ->call('lookup')
        ->assertSet('lookupFailed', true)
        ->assertSet('membershipId', null);
});

it('shows an informational message instead of a payment form for a membership pending review', function () {
    $membership = UserMembership::create([
        'membership_tier_id' => MembershipTier::factory()->create()->id,
        'status' => MembershipStatus::PendingReview,
        'membership_number' => 'ICEN/2026/00077',
        'email' => 'pending@example.com',
    ]);

    Livewire::test(\App\Livewire\Portal\RenewalFlow::class)
        ->set('lookupMembershipNumber', 'ICEN/2026/00077')
        ->set('lookupEmail', 'pending@example.com')
        ->call('lookup')
        ->assertSet('membershipId', $membership->id)
        ->assertSee('still under review');
});

it('creates and links a user account and invites a guest to set a password after a renewal payment succeeds', function () {
    Notification::fake();

    Http::fake([
        '*/transaction/verify/*' => Http::response([
            'status' => true,
            'data' => ['status' => 'success', 'reference' => 'ICEN-GUESTRENEW', 'channel' => 'card'],
        ]),
    ]);

    $tier = MembershipTier::factory()->create(['renewal_fee' => 15000]);

    $membership = UserMembership::create([
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Expired,
        'email' => 'claimme@example.com',
        'expires_at' => now()->subDays(5),
    ]);

    $renewal = MembershipRenewal::create(['user_membership_id' => $membership->id]);

    $gateway = \App\Models\PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    $payment = \App\Models\Payment::create([
        'payment_gateway_id' => $gateway->id,
        'payable_id' => $renewal->id,
        'payable_type' => MembershipRenewal::class,
        'amount' => 15000,
        'currency' => 'NGN',
        'reference' => 'ICEN-GUESTRENEW',
    ]);

    $renewal->update(['payment_id' => $payment->id]);

    $this->get('/payments/callback?reference=ICEN-GUESTRENEW')
        ->assertRedirectToRoute('member.dashboard');

    $membership->refresh();
    expect($membership->status)->toBe(MembershipStatus::Active)
        ->and($membership->user_id)->not->toBeNull();

    $user = User::find($membership->user_id);
    expect($user->email)->toBe('claimme@example.com')
        ->and($payment->fresh()->user_id)->toBe($user->id);

    $this->assertAuthenticatedAs($user);

    Notification::assertSentTo($user, \App\Notifications\SetPasswordInvite::class);
});

it('does not re-invite a member who already has a working password after a renewal payment succeeds', function () {
    Notification::fake();

    Http::fake([
        '*/transaction/verify/*' => Http::response([
            'status' => true,
            'data' => ['status' => 'success', 'reference' => 'ICEN-KNOWNPASS', 'channel' => 'card'],
        ]),
    ]);

    $tier = MembershipTier::factory()->create(['renewal_fee' => 15000]);
    $user = User::factory()->create(['password_set_at' => now()]);

    $membership = UserMembership::create([
        'user_id' => $user->id,
        'membership_tier_id' => $tier->id,
        'status' => MembershipStatus::Expired,
        'expires_at' => now()->subDays(5),
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
        'reference' => 'ICEN-KNOWNPASS',
    ]);

    $renewal->update(['payment_id' => $payment->id]);

    $this->get('/payments/callback?reference=ICEN-KNOWNPASS')->assertRedirect();

    Notification::assertNotSentTo($user, \App\Notifications\SetPasswordInvite::class);
});
