<?php

namespace App\Actions\Payments;

use App\Mail\BankTransferAwaitingVerification;
use App\Mail\BankTransferSubmissionReceived;
use App\Models\MembershipRenewal;
use App\Models\Payment;
use App\Models\UserMembership;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Mail;

class HandleBankTransferSubmission
{
    /**
     * Notify the applicant that their bank transfer + proof of payment was
     * received, and alert staff that it's awaiting manual verification.
     * Unlike Paystack, a bank transfer has no automatic confirmation, so
     * without this nothing tells either party anything happened.
     */
    public function handle(Payment $payment): void
    {
        $payable = $payment->payable()->first();

        [$email, $name, $context] = match (true) {
            $payable instanceof UserMembership => [
                $payable->email,
                $payable->fullName() ?: $payable->email,
                $payable->previous_membership_id ? 'level_change' : 'registration',
            ],
            $payable instanceof MembershipRenewal => [
                $payable->userMembership->email,
                $payable->userMembership->fullName() ?: $payable->userMembership->email,
                'renewal',
            ],
            default => [null, null, null],
        };

        if (! $email) {
            return;
        }

        Mail::to($email)->send(new BankTransferSubmissionReceived($payment, $name));

        $recipients = app(GeneralSettings::class)->payment_notification_recipients;

        if (! empty($recipients)) {
            Mail::to($recipients)->send(new BankTransferAwaitingVerification($payment, $name, $context));
        }
    }
}
