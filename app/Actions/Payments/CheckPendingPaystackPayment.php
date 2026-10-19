<?php

namespace App\Actions\Payments;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Jobs\VerifyPaystackPayment;
use App\Mail\PaystackPaymentUnresolved;
use App\Models\Payment;
use App\Services\Paystack\PaystackClient;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Mail;

/**
 * The single place that knows how to resolve a Paystack payment stuck in
 * `Pending` — called from the payment callback (the first, immediate check),
 * from {@see VerifyPaystackPayment} (the delayed queued retries), from the
 * scheduled sweep command (the backstop for when the queue didn't run), and
 * from the manual "Recheck with Paystack" action in the admin panel. Keeping
 * all four entry points behind one action means the retry budget and the
 * "give up and alert staff" behaviour can't drift out of sync between them.
 */
class CheckPendingPaystackPayment
{
    /** Stop auto-retrying after this many checks and alert staff instead. */
    public const MAX_ATTEMPTS = 5;

    /** Gap between automated retries. */
    public const RETRY_DELAY_SECONDS = 30;

    public function __construct(
        protected HandleSuccessfulPayment $handleSuccessfulPayment,
        protected PaystackClient $paystack,
    ) {}

    /**
     * @return 'resolved'|'still_pending'|'exhausted'|'skipped' What happened,
     *                                                          mainly so callers (tests, the manual Filament action) can
     *                                                          report something meaningful without re-deriving it from state.
     */
    public function handle(Payment $payment): string
    {
        if ($payment->status !== PaymentStatus::Pending || $payment->payment_method !== PaymentMethod::Paystack) {
            return 'skipped';
        }

        $data = $this->paystack->verifyTransaction($payment->reference)['data'] ?? [];

        return $this->applyResult($payment, $data);
    }

    /**
     * Same branching as {@see handle()}, but for a caller (the payment
     * callback) that already has a fresh verify-transaction response and
     * would otherwise be making a redundant, back-to-back Paystack API call.
     *
     * @param  array<string, mixed>  $data  The `data` node of a verify-transaction response.
     * @return 'resolved'|'still_pending'|'exhausted'|'skipped'
     */
    public function handleWithResult(Payment $payment, array $data): string
    {
        if ($payment->status !== PaymentStatus::Pending || $payment->payment_method !== PaymentMethod::Paystack) {
            return 'skipped';
        }

        return $this->applyResult($payment, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return 'resolved'|'still_pending'|'exhausted'
     */
    protected function applyResult(Payment $payment, array $data): string
    {
        $gatewayStatus = $data['status'] ?? null;

        if ($gatewayStatus === 'success') {
            $this->handleSuccessfulPayment->handle($payment, $data);
            $this->stopRetrying($payment);

            return 'resolved';
        }

        if (in_array($gatewayStatus, ['failed', 'abandoned', 'reversed'], true)) {
            $payment->update(['status' => $gatewayStatus === 'abandoned' ? PaymentStatus::Abandoned : PaymentStatus::Failed]);
            $this->stopRetrying($payment);

            return 'resolved';
        }

        return $this->reschedule($payment);
    }

    /**
     * Manually triggered from the admin panel — gives the payment a fresh
     * retry budget (rather than just re-running a single check) so it goes
     * through the same automated chain as a brand-new callback would.
     *
     * @return 'resolved'|'still_pending'|'skipped'
     */
    public function recheckManually(Payment $payment): string
    {
        $payment->update(['paystack_check_attempts' => 0]);

        $result = $this->handle($payment);

        return $result === 'exhausted' ? 'still_pending' : $result;
    }

    protected function reschedule(Payment $payment): string
    {
        $attempts = $payment->paystack_check_attempts + 1;

        if ($attempts >= self::MAX_ATTEMPTS) {
            $payment->update(['paystack_check_attempts' => $attempts, 'next_paystack_check_at' => null]);
            $this->alertStaff($payment);

            return 'exhausted';
        }

        $nextCheckAt = now()->addSeconds(self::RETRY_DELAY_SECONDS);
        $payment->update(['paystack_check_attempts' => $attempts, 'next_paystack_check_at' => $nextCheckAt]);

        VerifyPaystackPayment::dispatch($payment->id)->delay($nextCheckAt);

        return 'still_pending';
    }

    protected function stopRetrying(Payment $payment): void
    {
        $payment->update(['next_paystack_check_at' => null]);
    }

    protected function alertStaff(Payment $payment): void
    {
        $recipients = app(GeneralSettings::class)->payment_notification_recipients;

        if (empty($recipients)) {
            return;
        }

        Mail::to($recipients)->send(new PaystackPaymentUnresolved($payment));
    }
}
