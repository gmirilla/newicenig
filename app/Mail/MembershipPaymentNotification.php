<?php

namespace App\Mail;

use App\Models\Payment;
use App\Models\UserMembership;
use App\Services\Certificates\MemberInformationPdf;
use App\Services\MemberDocuments\ApplicationDocumentAttachments;
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

    /** @var array{attachments: array<int, Attachment>, attached: array<int, string>, skipped: array<int, string>}|null */
    protected ?array $documents = null;

    /**
     * @param  'registration'|'renewal'|'level_change'  $context
     */
    public function __construct(
        public UserMembership $membership,
        public Payment $payment,
        public string $context,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->context) {
            'renewal' => "Membership renewal received — {$this->membership->fullName()}",
            'level_change' => "Membership level change payment received — {$this->membership->fullName()}",
            default => "New membership payment received — {$this->membership->fullName()}",
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $documents = $this->documents();

        return new Content(
            markdown: 'mail.membership.payment-notification',
            with: [
                'attachedDocuments' => $documents['attached'],
                'skippedDocuments' => $documents['skipped'],
            ],
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
            ...$this->documents()['attachments'],
        ];
    }

    /**
     * The applicant's uploaded documents. Only new applications and level
     * changes carry them — a renewal doesn't upload anything. Resolved lazily
     * (and once) at send time so nothing file-related is serialized onto the
     * queue.
     *
     * @return array{attachments: array<int, Attachment>, attached: array<int, string>, skipped: array<int, string>}
     */
    protected function documents(): array
    {
        return $this->documents ??= $this->context === 'renewal'
            ? ['attachments' => [], 'attached' => [], 'skipped' => []]
            : app(ApplicationDocumentAttachments::class)->for($this->membership);
    }
}
