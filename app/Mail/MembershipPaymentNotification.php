<?php

namespace App\Mail;

use App\Models\Payment;
use App\Models\UserMembership;
use App\Services\Certificates\MemberInformationPdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MembershipPaymentNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  'registration'|'renewal'  $context
     */
    public function __construct(
        public UserMembership $membership,
        public Payment $payment,
        public string $context,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->context === 'renewal'
            ? "Membership renewal received — {$this->membership->fullName()}"
            : "New membership payment received — {$this->membership->fullName()}";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.membership.payment-notification',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $filename = 'member-'.str_replace('/', '-', $this->membership->membership_number ?? (string) $this->membership->id).'.pdf';

        return [
            Attachment::fromData(
                fn () => app(MemberInformationPdf::class)->build($this->membership)->output(),
                $filename,
            )->withMime('application/pdf'),
        ];
    }
}
