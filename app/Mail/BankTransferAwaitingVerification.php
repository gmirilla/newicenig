<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BankTransferAwaitingVerification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  'registration'|'renewal'|'level_change'  $context
     */
    public function __construct(
        public Payment $payment,
        public string $applicantName,
        public string $context,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Bank transfer awaiting verification — {$this->applicantName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.payments.bank-transfer-awaiting-verification',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $media = $this->payment->getFirstMedia('proof_of_payment');

        if (! $media) {
            return [];
        }

        return [
            Attachment::fromPath($media->getPath())
                ->as($media->file_name)
                ->withMime($media->mime_type),
        ];
    }
}
