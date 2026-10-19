<?php

namespace App\Jobs;

use App\Actions\Payments\CheckPendingPaystackPayment;
use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * One delayed retry in the "is this Paystack payment still pending?" chain.
 * {@see CheckPendingPaystackPayment} does the actual work, including
 * re-dispatching this job for the next attempt when the payment is still
 * unresolved — this class is just the queue entry point.
 *
 * Unique per payment so a webhook landing mid-chain, or the scheduled sweep
 * command, can't end up racing an in-flight retry of the same payment.
 */
class VerifyPaystackPayment implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $uniqueFor = 20;

    public function __construct(
        public int $paymentId,
    ) {}

    public function uniqueId(): string
    {
        return (string) $this->paymentId;
    }

    public function handle(CheckPendingPaystackPayment $check): void
    {
        $payment = Payment::find($this->paymentId);

        if ($payment) {
            $check->handle($payment);
        }
    }
}
