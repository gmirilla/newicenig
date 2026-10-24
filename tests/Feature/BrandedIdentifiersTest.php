<?php

use App\Models\MembershipTier;
use App\Models\Payment;
use App\Models\UserMembership;
use App\Settings\BrandingSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('prefixes generated payment references with the configured brand short name', function () {
    app(BrandingSettings::class)->fill(['brand_short_name' => 'acme'])->save();

    expect(Payment::generateReference())->toStartWith('ACME-');
});

it('sanitizes non-alphanumeric characters out of the payment reference prefix', function () {
    app(BrandingSettings::class)->fill(['brand_short_name' => 'A-C/M E'])->save();

    expect(Payment::generateReference())->toStartWith('ACME-');
});

it('falls back to a generic payment reference prefix if the short name has nothing alphanumeric in it', function () {
    app(BrandingSettings::class)->fill(['brand_short_name' => '---'])->save();

    expect(Payment::generateReference())->toStartWith('PAY-');
});

it('prefixes generated membership numbers with the configured brand short name', function () {
    app(BrandingSettings::class)->fill(['brand_short_name' => 'ACME'])->save();

    $tier = MembershipTier::factory()->create();
    UserMembership::create(['membership_tier_id' => $tier->id, 'status' => 'pending_payment', 'membership_number' => UserMembership::generateMembershipNumber()]);

    expect(UserMembership::generateMembershipNumber())->toMatch('/^ACME\/\d{4}\/\d{5}$/');
});
