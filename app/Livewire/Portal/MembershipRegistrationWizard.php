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

    public ?int $membershipTierId = null;

    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $phone = '';

    public string $employer_name = '';

    public string $job_title = '';

    public ?int $years_of_experience = null;

    public string $state_of_origin = '';

    public string $qualification = '';

    public ?string $date_of_birth = null;

    public $qualification_document = null;

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

    public function continueToReview(): void
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'state_of_origin' => ['nullable', 'string', 'max:255'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
        ];

        if ($this->selectedTier?->requires_employer_info) {
            $rules['employer_name'] = ['required', 'string', 'max:255'];
            $rules['job_title'] = ['required', 'string', 'max:255'];
        }

        if ($this->selectedTier?->requires_qualification_upload) {
            $rules['qualification_document'] = ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'];
        }

        $this->validate($rules);

        $this->step = 3;
    }

    public function submitAndPay()
    {
        $tier = $this->selectedTier;

        abort_unless($tier, 404);

        $membership = UserMembership::create([
            'membership_tier_id' => $tier->id,
            'status' => MembershipStatus::PendingPayment,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'employer_name' => $this->employer_name ?: null,
            'job_title' => $this->job_title ?: null,
            'years_of_experience' => $this->years_of_experience,
            'state_of_origin' => $this->state_of_origin ?: null,
            'qualification' => $this->qualification ?: null,
            'date_of_birth' => $this->date_of_birth,
            'directory_opt_in' => $this->directory_opt_in,
        ]);

        if ($this->qualification_document) {
            $file = MemberFile::create([
                'user_membership_id' => $membership->id,
                'type' => 'qualification_certificate',
            ]);

            $file->addMedia($this->qualification_document->getRealPath())
                ->usingFileName($this->qualification_document->getClientOriginalName())
                ->toMediaCollection('file');
        }

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
}
