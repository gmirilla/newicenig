<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent once a Paystack payment has gone unconfirmed for 5 automated checks
 * (see App\Actions\Payments\CheckPendingPaystackPayment) — Paystack never
 * reported success, failure or abandonment, so it needs a human to look at
 * it directly in the Paystack dashboard or retry it from the admin panel.
 */
class PaystackPaymentUnresolved extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Payment $payment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Payment still unconfirmed — {$this->payment->reference}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.payments.paystack-payment-unresolved',
        );
    }
}
