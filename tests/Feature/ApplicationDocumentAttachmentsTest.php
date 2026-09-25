<?php

use App\Enums\MembershipStatus;
use App\Mail\MembershipPaymentNotification;
use App\Models\MemberFile;
use App\Models\MembershipTier;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\UserMembership;
use App\Services\MemberDocuments\ApplicationDocumentAttachments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(fn () => Storage::fake('public'));

function applicationWithMembership(): UserMembership
{
    return UserMembership::create([
        'membership_tier_id' => MembershipTier::factory()->create()->id,
        'status' => MembershipStatus::PendingReview,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
        'membership_number' => 'ICEN/2026/007',
    ]);
}

function addApplicationDocument(UserMembership $membership, string $type, UploadedFile $upload): MemberFile
{
    $file = MemberFile::create(['user_membership_id' => $membership->id, 'type' => $type]);

    $file->addMedia($upload->getRealPath())
        ->preservingOriginal()
        ->usingFileName($upload->getClientOriginalName())
        ->toMediaCollection('file');

    return $file;
}

function notificationFor(UserMembership $membership, string $context = 'registration'): MembershipPaymentNotification
{
    $gateway = PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    $payment = Payment::create([
        'payment_gateway_id' => $gateway->id,
        'payable_id' => $membership->id,
        'payable_type' => UserMembership::class,
        'amount' => 35000,
        'currency' => 'NGN',
        'reference' => 'ICEN-DOCS-'.$membership->id.'-'.$context,
    ]);

    return new MembershipPaymentNotification($membership, $payment, $context);
}

it('attaches every uploaded document under a safe filename that ignores the uploaded name', function () {
    $membership = applicationWithMembership();

    addApplicationDocument($membership, 'passport_photo', UploadedFile::fake()->image('../../evil name.png'));
    addApplicationDocument($membership, 'higher_institution_certificate', UploadedFile::fake()->create('my degree (final).pdf', 200, 'application/pdf'));

    $result = app(ApplicationDocumentAttachments::class)->for($membership);

    expect($result['attachments'])->toHaveCount(2)
        ->and($result['skipped'])->toBe([])
        ->and($result['attached'])->toBe(['Higher institution certificate', 'Passport photo'])
        ->and(collect($result['attachments'])->pluck('as')->all())->toBe([
            'ICEN-2026-007-higher_institution_certificate.pdf',
            'ICEN-2026-007-passport_photo.png',
        ]);
});

it('skips documents that would exceed the size budget, keeping the most important first', function () {
    $membership = applicationWithMembership();

    addApplicationDocument($membership, 'passport_photo', UploadedFile::fake()->createWithContent('photo.pdf', str_repeat('a', 3 * 1024 * 1024)));
    addApplicationDocument($membership, 'primary_school_certificate', UploadedFile::fake()->createWithContent('primary.pdf', str_repeat('a', 3 * 1024 * 1024)));
    addApplicationDocument($membership, 'higher_institution_certificate', UploadedFile::fake()->createWithContent('degree.pdf', str_repeat('a', 3 * 1024 * 1024)));

    $result = app(ApplicationDocumentAttachments::class)->for($membership);

    expect($result['attached'])->toBe(['Higher institution certificate', 'Primary school certificate'])
        ->and($result['skipped'])->toBe(['Passport photo (too large to attach)']);
});

it('skips a document that is missing on disk instead of failing', function () {
    $membership = applicationWithMembership();

    addApplicationDocument($membership, 'passport_photo', UploadedFile::fake()->image('photo.png'));
    $degree = addApplicationDocument($membership, 'higher_institution_certificate', UploadedFile::fake()->create('degree.pdf', 100, 'application/pdf'));

    $media = $degree->getFirstMedia('file');
    Storage::disk($media->disk)->delete($media->getPathRelativeToRoot());

    $result = app(ApplicationDocumentAttachments::class)->for($membership);

    expect($result['attached'])->toBe(['Passport photo'])
        ->and($result['skipped'])->toBe(['Higher institution certificate (file not found)']);
});

it('adds the applicant documents to the notification email for a new application', function () {
    $membership = applicationWithMembership();
    addApplicationDocument($membership, 'passport_photo', UploadedFile::fake()->image('photo.png'));

    $mail = notificationFor($membership);

    // Member information PDF + the passport photo.
    expect($mail->attachments())->toHaveCount(2);
    $mail->assertSeeInHtml('Uploaded documents attached: Passport photo');
});

it('lists documents that could not be attached in the email body', function () {
    $membership = applicationWithMembership();
    $degree = addApplicationDocument($membership, 'higher_institution_certificate', UploadedFile::fake()->create('degree.pdf', 100, 'application/pdf'));

    $media = $degree->getFirstMedia('file');
    Storage::disk($media->disk)->delete($media->getPathRelativeToRoot());

    $mail = notificationFor($membership);

    expect($mail->attachments())->toHaveCount(1);
    $mail->assertSeeInHtml('Not attached');
    $mail->assertSeeInHtml('Higher institution certificate (file not found)');
});

it('does not attach any applicant documents to a renewal notification', function () {
    $membership = applicationWithMembership();
    addApplicationDocument($membership, 'passport_photo', UploadedFile::fake()->image('photo.png'));

    $mail = notificationFor($membership, 'renewal');

    expect($mail->attachments())->toHaveCount(1);
    $mail->assertDontSeeInHtml('Uploaded documents attached');
});
