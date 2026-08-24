<?php

namespace App\Actions\Payments;

use App\Enums\MembershipStatus;
use App\Enums\PaymentStatus;
use App\Models\MembershipRenewal;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserMembership;
use App\Notifications\SetPasswordInvite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HandleSuccessfulPayment
{
    /**
     * @param  array<string, mixed>  $verifiedData  The `data` node of Paystack's verify-transaction response.
     */
    public function handle(Payment $payment, array $verifiedData): void
    {
        if ($payment->status === PaymentStatus::Successful) {
            return;
        }

        $payment->update([
            'status' => PaymentStatus::Successful,
            'gateway_reference' => $verifiedData['reference'] ?? $payment->reference,
            'channel' => $verifiedData['channel'] ?? null,
            'paid_at' => now(),
            'raw_gateway_response' => $verifiedData,
        ]);

        $payable = $payment->payable()->first();

        if ($payable instanceof UserMembership) {
            $this->handleMembershipPayment($payable);
        } elseif ($payable instanceof MembershipRenewal) {
            $this->handleRenewalPayment($payable);
        }
    }

    protected function handleMembershipPayment(UserMembership $membership): void
    {
        $user = $membership->user;

        if (! $user && $membership->email) {
            $user = User::where('email', $membership->email)->first();

            $isNewUser = false;

            if (! $user) {
                $user = User::create([
                    'name' => $membership->fullName() ?: $membership->email,
                    'email' => $membership->email,
                    'phone' => $membership->phone,
                    'password' => Hash::make(Str::random(40)),
                ]);

                $isNewUser = true;
            }

            $membership->update(['user_id' => $user->id]);

            if ($isNewUser) {
                $user->notify(new SetPasswordInvite);
            }
        }

        $membership->update([
            'status' => MembershipStatus::PendingReview,
            'submitted_at' => now(),
        ]);
    }

    protected function handleRenewalPayment(MembershipRenewal $renewal): void
    {
        $membership = $renewal->userMembership;

        $previousExpiry = $membership->expires_at ?? now();
        $newExpiry = $previousExpiry->isFuture() ? $previousExpiry->copy()->addYear() : now()->addYear();

        $renewal->update([
            'renewed_at' => now(),
            'previous_expires_at' => $previousExpiry,
            'new_expires_at' => $newExpiry,
        ]);

        $membership->update([
            'status' => MembershipStatus::Active,
            'expires_at' => $newExpiry,
        ]);
    }
}
