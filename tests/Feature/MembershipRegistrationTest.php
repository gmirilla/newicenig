<?php

use App\Enums\MembershipStatus;
use App\Enums\PaymentStatus;
use App\Models\MembershipTier;
use App\Models\Payment;
use App\Models\UserMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('walks through the full registration wizard and redirects to paystack', function () {
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
    ]);

    Livewire::test(\App\Livewire\Portal\MembershipRegistrationWizard::class)
        ->call('selectTier', $tier->id)
        ->assertSet('step', 2)
        // Step 2: personal details
        ->set('first_name', 'Jane')
        ->set('middle_name', 'A.')
        ->set('last_name', 'Doe')
        ->set('gender', 'female')
        ->set('date_of_birth', '1990-01-01')
        ->set('nationality', 'Nigerian')
        ->set('marital_status', 'single')
        ->set('passport_photo', UploadedFile::fake()->image('passport.jpg'))
        ->call('continuePersonalDetails')
        ->assertHasNoErrors()
        ->assertSet('step', 3)
        // Step 3: contact & address
        ->set('email', 'jane@example.com')
        ->set('phone', '08012345678')
        ->set('state_of_origin', 'Lagos')
        ->set('residential_address', '1 Marina Street, Lagos')
        ->call('continueContactAddress')
        ->assertHasNoErrors()
        ->assertSet('step', 4)
        // Step 4: educational qualifications
        ->set('higher_institution', 'University of Lagos')
        ->set('higher_institution_course', 'Economics')
        ->set('higher_institution_year', '2012')
        ->set('higher_institution_certificate_file', UploadedFile::fake()->create('degree.pdf', 200))
        ->call('continueEducation')
        ->assertHasNoErrors()
        ->assertSet('step', 5)
        // Step 5: professional background
        ->set('year_of_qualification', '2013')
        ->call('continueProfessionalBackground')
        ->assertHasNoErrors()
        ->assertSet('step', 6)
        // Step 6: declaration
        ->set('declaration_name', 'Jane A. Doe')
        ->set('declaration_agreed', true)
        ->call('continueDeclaration')
        ->assertHasNoErrors()
        ->assertSet('step', 7)
        // Step 7: review & pay
        ->call('submitAndPay')
        ->assertRedirect('https://checkout.paystack.com/abc123');

    $membership = UserMembership::first();
    expect($membership)->not->toBeNull()
        ->and($membership->status)->toBe(MembershipStatus::PendingPayment)
        ->and($membership->email)->toBe('jane@example.com')
        ->and($membership->higher_institution)->toBe('University of Lagos')
        ->and($membership->declaration_name)->toBe('Jane A. Doe')
        ->and($membership->declaration_accepted_at)->not->toBeNull();

    expect($membership->files()->count())->toBe(2);
    expect($membership->files()->where('type', 'passport_photo')->exists())->toBeTrue();
    expect($membership->files()->where('type', 'higher_institution_certificate')->exists())->toBeTrue();

    $payment = Payment::first();
    expect($payment)->not->toBeNull()
        ->and($payment->status)->toBe(PaymentStatus::Pending)
        ->and((float) $payment->amount)->toBe(35000.0);

    Http::assertSent(fn ($request) => $request->url() === 'https://api.paystack.co/transaction/initialize'
        && $request['email'] === 'jane@example.com'
        && $request['amount'] === 3500000);
});

it('blocks moving past the personal details step without required fields', function () {
    $tier = MembershipTier::factory()->create();

    Livewire::test(\App\Livewire\Portal\MembershipRegistrationWizard::class)
        ->call('selectTier', $tier->id)
        ->call('continuePersonalDetails')
        ->assertHasErrors(['first_name', 'last_name', 'gender', 'date_of_birth', 'marital_status', 'passport_photo'])
        ->assertSet('step', 2);
});

function advanceToStep5(\App\Models\MembershipTier $tier)
{
    return Livewire::test(\App\Livewire\Portal\MembershipRegistrationWizard::class)
        ->call('selectTier', $tier->id)
        ->set('first_name', 'Jane')
        ->set('last_name', 'Doe')
        ->set('gender', 'female')
        ->set('date_of_birth', '1990-01-01')
        ->set('nationality', 'Nigerian')
        ->set('marital_status', 'single')
        ->set('passport_photo', UploadedFile::fake()->image('passport.jpg'))
        ->call('continuePersonalDetails')
        ->set('email', 'jane@example.com')
        ->set('phone', '08012345678')
        ->set('state_of_origin', 'Lagos')
        ->set('residential_address', '1 Marina Street, Lagos')
        ->call('continueContactAddress')
        ->set('higher_institution', 'University of Lagos')
        ->set('higher_institution_course', 'Economics')
        ->set('higher_institution_year', '2012')
        ->set('higher_institution_certificate_file', UploadedFile::fake()->create('degree.pdf', 200))
        ->call('continueEducation')
        ->assertSet('step', 5);
}

it('requires employer info at the professional background step when the tier demands it', function () {
    $tier = MembershipTier::factory()->create(['requires_employer_info' => true]);

    advanceToStep5($tier)
        ->set('year_of_qualification', '2013')
        ->call('continueProfessionalBackground')
        ->assertHasErrors(['employer_name', 'job_title'])
        ->assertSet('step', 5);
});

it('requires the other institute fields only when the applicant says they belong to one', function () {
    $tier = MembershipTier::factory()->create(['requires_employer_info' => false]);

    advanceToStep5($tier)
        ->set('year_of_qualification', '2013')
        ->set('belongs_to_other_institute', true)
        ->call('continueProfessionalBackground')
        ->assertHasErrors(['other_institute_name', 'other_institute_status'])
        ->assertSet('step', 5);
});
