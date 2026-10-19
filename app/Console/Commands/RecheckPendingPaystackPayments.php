<?php

namespace App\Console\Commands;

use App\Actions\Payments\CheckPendingPaystackPayment;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Backstop for the delayed-queue-job retry chain in
 * CheckPendingPaystackPayment: resolves any Paystack payment whose next
 * scheduled check is overdue, independent of the queue worker. The queued
 * jobs are what give exact 30-second retries in the normal case; this sweep
 * only matters when the queue worker's own cron missed a run, so it checks
 * directly rather than going through the queue.
 */
#[Signature('app:recheck-pending-paystack-payments')]
#[Description('Resolve Paystack payments still pending whose automated recheck is overdue')]
class RecheckPendingPaystackPayments extends Command
{
    public function handle(CheckPendingPaystackPayment $check): void
    {
        $payments = Payment::query()
            ->where('payment_method', PaymentMethod::Paystack)
            ->where('status', PaymentStatus::Pending)
            ->whereNotNull('next_paystack_check_at')
            ->where('next_paystack_check_at', '<=', now())
            ->get();

        foreach ($payments as $payment) {
            $check->handle($payment);
        }

        $this->info("Rechecked {$payments->count()} overdue pending payment(s).");
    }
}
