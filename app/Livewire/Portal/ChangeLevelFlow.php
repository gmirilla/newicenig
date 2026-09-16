<?php

namespace App\Livewire\Portal;

use App\Enums\MembershipStatus;
use App\Livewire\Portal\Concerns\HandlesPaymentMethod;
use App\Models\MembershipTier;
use App\Models\UserMembership;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class ChangeLevelFlow extends Component
{
    use HandlesPaymentMethod, WithFileUploads;

    public ?int $selectedTierId = null;

    public function getMembershipProperty(): ?UserMembership
    {
        return Auth::user()->currentMembership();
    }

    public function getAvailableTiersProperty()
    {
        $membership = $this->membership;

        if (! $membership) {
            return collect();
        }

        return MembershipTier::where('is_active', true)
            ->where('id', '!=', $membership->membership_tier_id)
            ->where('registration_fee', '>', $membership->membershipTier->registration_fee)
            ->orderBy('sort_order')
            ->get();
    }

    public function getSelectedTierProperty(): ?MembershipTier
    {
        return $this->selectedTierId ? $this->availableTiers->firstWhere('id', $this->selectedTierId) : null;
    }

    public function selectTier(int $tierId): void
    {
        abort_unless($this->availableTiers->firstWhere('id', $tierId), 404);

        $this->selectedTierId = $tierId;
    }

    protected function paymentTier(): ?MembershipTier
    {
        return $this->selectedTier;
    }

    protected function paymentFeeType(): string
    {
        return 'registration';
    }

    public function submit()
    {
        $membership = $this->membership;

        abort_unless($membership && $membership->isActive(), 404);

        $tier = $this->selectedTier;

        abort_unless($tier, 404);

        $application = UserMembership::create([
            'membership_tier_id' => $tier->id,
            'status' => MembershipStatus::PendingPayment,
            'previous_membership_id' => $membership->id,
            'first_name' => $membership->first_name,
            'middle_name' => $membership->middle_name,
            'last_name' => $membership->last_name,
            'email' => $membership->email,
            'phone' => $membership->phone,
            'gender' => $membership->gender,
            'employer_name' => $membership->employer_name,
            'job_title' => $membership->job_title,
            'years_of_experience' => $membership->years_of_experience,
            'state_of_origin' => $membership->state_of_origin,
            'qualification' => $membership->qualification,
            'date_of_birth' => $membership->date_of_birth,
            'place_of_birth' => $membership->place_of_birth,
            'nationality' => $membership->nationality,
            'country_of_residence' => $membership->country_of_residence,
            'is_permanent_resident' => $membership->is_permanent_resident,
            'local_government_area' => $membership->local_government_area,
            'marital_status' => $membership->marital_status,
            'residential_address' => $membership->residential_address,
            'postal_address' => $membership->postal_address,
            'next_of_kin_name' => $membership->next_of_kin_name,
            'next_of_kin_address' => $membership->next_of_kin_address,
            'primary_school' => $membership->primary_school,
            'primary_school_year' => $membership->primary_school_year,
            'secondary_school' => $membership->secondary_school,
            'secondary_school_year' => $membership->secondary_school_year,
            'higher_institution' => $membership->higher_institution,
            'higher_institution_course' => $membership->higher_institution_course,
            'higher_institution_year' => $membership->higher_institution_year,
            'higher_institution_grade' => $membership->higher_institution_grade,
            'higher_institution_second_degree' => $membership->higher_institution_second_degree,
            'belongs_to_other_institute' => $membership->belongs_to_other_institute,
            'other_institute_name' => $membership->other_institute_name,
            'other_institute_status' => $membership->other_institute_status,
            'other_institute_membership_number' => $membership->other_institute_membership_number,
            'year_of_qualification' => $membership->year_of_qualification,
            'extra_fields' => $membership->extra_fields,
            'directory_opt_in' => $membership->directory_opt_in,
        ]);

        $payment = $this->createPayment($application);

        return $this->redirectForPayment($payment, Auth::user()->email, ['user_membership_id' => $application->id, 'level_change' => true]);
    }

    public function render()
    {
        return view('livewire.portal.change-level-flow');
    }
}
