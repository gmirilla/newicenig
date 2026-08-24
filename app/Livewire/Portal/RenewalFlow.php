<?php

namespace App\Livewire\Portal;

use App\Models\MembershipRenewal;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Services\Paystack\PaystackClient;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RenewalFlow extends Component
{
    public function getMembershipProperty()
    {
        return Auth::user()->currentMembership();
    }

    public function renew()
    {
        $membership = $this->membership;

        abort_unless($membership, 404);

        $renewal = MembershipRenewal::create([
            'user_membership_id' => $membership->id,
        ]);

        $gateway = PaymentGateway::firstOrCreate(
            ['slug' => 'paystack'],
            ['name' => 'Paystack', 'is_active' => true],
        );

        $payment = Payment::create([
            'user_id' => Auth::id(),
            'payment_gateway_id' => $gateway->id,
            'payable_id' => $renewal->id,
            'payable_type' => MembershipRenewal::class,
            'amount' => $membership->membershipTier->renewal_fee,
            'currency' => $membership->membershipTier->currency,
            'reference' => Payment::generateReference(),
        ]);

        $renewal->update(['payment_id' => $payment->id]);

        $paystack = app(PaystackClient::class);

        $response = $paystack->initializeTransaction(
            email: Auth::user()->email,
            amountInKobo: (int) round($membership->membershipTier->renewal_fee * 100),
            reference: $payment->reference,
            callbackUrl: route('payments.callback'),
            metadata: ['membership_renewal_id' => $renewal->id],
        );

        return $this->redirect($response['data']['authorization_url'], navigate: false);
    }

    public function render()
    {
        return view('livewire.portal.renewal-flow');
    }
}
