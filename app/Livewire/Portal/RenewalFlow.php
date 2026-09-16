<?php

namespace App\Livewire\Portal;

use App\Livewire\Portal\Concerns\HandlesPaymentMethod;
use App\Models\MembershipRenewal;
use App\Models\MembershipTier;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class RenewalFlow extends Component
{
    use HandlesPaymentMethod, WithFileUploads;

    public function getMembershipProperty()
    {
        return Auth::user()->currentMembership();
    }

    protected function paymentTier(): ?MembershipTier
    {
        return $this->membership?->membershipTier;
    }

    protected function paymentFeeType(): string
    {
        return 'renewal';
    }

    public function renew()
    {
        $membership = $this->membership;

        abort_unless($membership, 404);

        $renewal = MembershipRenewal::create([
            'user_membership_id' => $membership->id,
        ]);

        $payment = $this->createPayment($renewal);

        $renewal->update(['payment_id' => $payment->id]);

        return $this->redirectForPayment($payment, Auth::user()->email, ['membership_renewal_id' => $renewal->id]);
    }

    public function render()
    {
        return view('livewire.portal.renewal-flow');
    }
}
