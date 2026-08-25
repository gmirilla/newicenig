<?php

namespace App\Livewire\Portal;

use App\Enums\MembershipStatus;
use App\Models\MemberFile;
use App\Models\MembershipTier;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\UserMembership;
use App\Services\Paystack\PaystackClient;
use Livewire\Component;
use Livewire\WithFileUploads;

class MembershipRegistrationWizard extends Component
{
    use WithFileUploads;

    public int $step = 1;

    public int $totalSteps = 7;

    public ?int $membershipTierId = null;

    // Step 2 — Personal details
    public string $first_name = '';

    public string $middle_name = '';

    public string $last_name = '';

    public string $gender = '';

    public ?string $date_of_birth = null;

    public string $place_of_birth = '';

    public string $nationality = 'Nigerian';

    public string $marital_status = '';

    public $passport_photo = null;

    // Step 3 — Contact & address
    public string $email = '';

    public string $phone = '';

    public string $state_of_origin = '';

    public string $local_government_area = '';

    public string $residential_address = '';

    public string $postal_address = '';

    public string $next_of_kin_name = '';

    public string $next_of_kin_address = '';

    // Step 4 — Educational qualifications
    public string $primary_school = '';

    public string $primary_school_year = '';

    public $primary_school_certificate_file = null;

    public string $secondary_school = '';

    public string $secondary_school_year = '';

    public $secondary_school_certificate_file = null;

    public string $higher_institution = '';

    public string $higher_institution_course = '';

    public string $higher_institution_year = '';

    public string $higher_institution_grade = '';

    public string $higher_institution_second_degree = '';

    public $higher_institution_certificate_file = null;

    // Step 5 — Professional background
    public string $employer_name = '';

    public string $job_title = '';

    public ?int $years_of_experience = null;

    public bool $belongs_to_other_institute = false;

    public string $other_institute_name = '';

    public string $other_institute_status = '';

    public string $other_institute_membership_number = '';

    public string $year_of_qualification = '';

    // Step 6 — Declaration
    public string $declaration_name = '';

    public bool $declaration_agreed = false;

    // Step 2 (kept for the directory opt-in, shown on the review step)
    public bool $directory_opt_in = false;

    public function mount(?MembershipTier $tier = null): void
    {
        if ($tier?->exists) {
            $this->membershipTierId = $tier->id;
            $this->step = 2;
        }
    }

    public function getSelectedTierProperty(): ?MembershipTier
    {
        return $this->membershipTierId ? MembershipTier::find($this->membershipTierId) : null;
    }

    public function selectTier(int $tierId): void
    {
        $this->membershipTierId = $tierId;
        $this->step = 2;
    }

    public function previousStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function continuePersonalDetails(): void
    {
        $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'nationality' => ['required', 'string', 'max:255'],
            'marital_status' => ['required', 'in:single,married,divorced,widowed'],
            'passport_photo' => ['required', 'image', 'max:2048'],
        ]);

        $this->step = 3;
    }

    public function continueContactAddress(): void
    {
        $this->validate([
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'state_of_origin' => ['required', 'string', 'max:255'],
            'local_government_area' => ['nullable', 'string', 'max:255'],
            'residential_address' => ['required', 'string', 'max:2000'],
            'postal_address' => ['nullable', 'string', 'max:2000'],
            'next_of_kin_name' => ['nullable', 'string', 'max:255'],
            'next_of_kin_address' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->step = 4;
    }

    public function continueEducation(): void
    {
        $this->validate([
            'primary_school' => ['nullable', 'string', 'max:255'],
            'primary_school_year' => ['nullable', 'string', 'max:9'],
            'primary_school_certificate_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'secondary_school' => ['nullable', 'string', 'max:255'],
            'secondary_school_year' => ['nullable', 'string', 'max:9'],
            'secondary_school_certificate_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'higher_institution' => ['required', 'string', 'max:255'],
            'higher_institution_course' => ['required', 'string', 'max:255'],
            'higher_institution_year' => ['required', 'string', 'max:9'],
            'higher_institution_grade' => ['nullable', 'string', 'max:255'],
            'higher_institution_second_degree' => ['nullable', 'string', 'max:255'],
            'higher_institution_certificate_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $this->step = 5;
    }

    public function continueProfessionalBackground(): void
    {
        $rules = [
            'belongs_to_other_institute' => ['boolean'],
            'other_institute_name' => ['required_if:belongs_to_other_institute,true', 'nullable', 'string', 'max:255'],
            'other_institute_status' => ['required_if:belongs_to_other_institute,true', 'nullable', 'string', 'max:255'],
            'other_institute_membership_number' => ['nullable', 'string', 'max:255'],
            'year_of_qualification' => ['required', 'string', 'max:9'],
        ];

        if ($this->selectedTier?->requires_employer_info) {
            $rules['employer_name'] = ['required', 'string', 'max:255'];
            $rules['job_title'] = ['required', 'string', 'max:255'];
        }

        $this->validate($rules);

        $this->step = 6;
    }

    public function continueDeclaration(): void
    {
        $this->validate([
            'declaration_name' => ['required', 'string', 'max:255'],
            'declaration_agreed' => ['accepted'],
        ]);

        $this->step = 7;
    }

    public function submitAndPay()
    {
        $tier = $this->selectedTier;

        abort_unless($tier, 404);

        $membership = UserMembership::create([
            'membership_tier_id' => $tier->id,
            'status' => MembershipStatus::PendingPayment,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name ?: null,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'place_of_birth' => $this->place_of_birth ?: null,
            'nationality' => $this->nationality,
            'marital_status' => $this->marital_status,
            'state_of_origin' => $this->state_of_origin,
            'local_government_area' => $this->local_government_area ?: null,
            'residential_address' => $this->residential_address,
            'postal_address' => $this->postal_address ?: null,
            'next_of_kin_name' => $this->next_of_kin_name ?: null,
            'next_of_kin_address' => $this->next_of_kin_address ?: null,
            'primary_school' => $this->primary_school ?: null,
            'primary_school_year' => $this->primary_school_year ?: null,
            'secondary_school' => $this->secondary_school ?: null,
            'secondary_school_year' => $this->secondary_school_year ?: null,
            'higher_institution' => $this->higher_institution,
            'higher_institution_course' => $this->higher_institution_course,
            'higher_institution_year' => $this->higher_institution_year,
            'higher_institution_grade' => $this->higher_institution_grade ?: null,
            'higher_institution_second_degree' => $this->higher_institution_second_degree ?: null,
            'employer_name' => $this->employer_name ?: null,
            'job_title' => $this->job_title ?: null,
            'years_of_experience' => $this->years_of_experience,
            'belongs_to_other_institute' => $this->belongs_to_other_institute,
            'other_institute_name' => $this->belongs_to_other_institute ? $this->other_institute_name : null,
            'other_institute_status' => $this->belongs_to_other_institute ? $this->other_institute_status : null,
            'other_institute_membership_number' => $this->belongs_to_other_institute ? $this->other_institute_membership_number : null,
            'year_of_qualification' => $this->year_of_qualification,
            'declaration_name' => $this->declaration_name,
            'declaration_accepted_at' => now(),
            'directory_opt_in' => $this->directory_opt_in,
        ]);

        $this->storeUpload($membership, $this->passport_photo, 'passport_photo');
        $this->storeUpload($membership, $this->primary_school_certificate_file, 'primary_school_certificate');
        $this->storeUpload($membership, $this->secondary_school_certificate_file, 'secondary_school_certificate');
        $this->storeUpload($membership, $this->higher_institution_certificate_file, 'higher_institution_certificate');

        $gateway = PaymentGateway::firstOrCreate(
            ['slug' => 'paystack'],
            ['name' => 'Paystack', 'is_active' => true],
        );

        $payment = Payment::create([
            'payment_gateway_id' => $gateway->id,
            'payable_id' => $membership->id,
            'payable_type' => UserMembership::class,
            'amount' => $tier->registration_fee,
            'currency' => $tier->currency,
            'reference' => Payment::generateReference(),
        ]);

        $paystack = app(PaystackClient::class);

        $response = $paystack->initializeTransaction(
            email: $this->email,
            amountInKobo: (int) round($tier->registration_fee * 100),
            reference: $payment->reference,
            callbackUrl: route('payments.callback'),
            metadata: ['user_membership_id' => $membership->id],
        );

        return $this->redirect($response['data']['authorization_url'], navigate: false);
    }

    protected function storeUpload(UserMembership $membership, $upload, string $type): void
    {
        if (! $upload) {
            return;
        }

        $file = MemberFile::create([
            'user_membership_id' => $membership->id,
            'type' => $type,
        ]);

        $file->addMedia($upload->getRealPath())
            ->usingFileName($upload->getClientOriginalName())
            ->toMediaCollection('file');
    }
}
