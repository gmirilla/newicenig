<?php

namespace App\Services\MemberDocuments;

use App\Models\MemberFile;
use App\Models\UserMembership;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Picks which of an application's uploaded documents can safely be attached
 * to an email. Attachments are capped by a total size budget, and anything
 * missing on disk or over budget is reported back instead of failing the whole
 * message — staff can still open those from the admin panel.
 */
class ApplicationDocumentAttachments
{
    /** Combined raw size of all attached documents; base64 in email adds ~37%. */
    public const MAX_TOTAL_BYTES = 8 * 1024 * 1024;

    /** Attach in this order, so the most important documents survive the size budget. */
    private const PRIORITY = [
        'higher_institution_certificate',
        'secondary_school_certificate',
        'primary_school_certificate',
        'passport_photo',
    ];

    private const EXTENSIONS = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    /**
     * @return array{attachments: array<int, Attachment>, attached: array<int, string>, skipped: array<int, string>}
     */
    public function for(UserMembership $membership): array
    {
        $result = ['attachments' => [], 'attached' => [], 'skipped' => []];
        $usedBytes = 0;

        foreach ($this->orderedFiles($membership) as $file) {
            $label = $this->label($file);
            $media = $file->getFirstMedia('file');

            if (! $media || ! Storage::disk($media->disk)->exists($media->getPathRelativeToRoot())) {
                Log::warning("Application document [{$file->type}] for membership #{$membership->id} is missing on disk; not attached.");
                $result['skipped'][] = "{$label} (file not found)";

                continue;
            }

            if ($usedBytes + $media->size > self::MAX_TOTAL_BYTES) {
                $result['skipped'][] = "{$label} (too large to attach)";

                continue;
            }

            $usedBytes += $media->size;
            $result['attached'][] = $label;
            $result['attachments'][] = Attachment::fromStorageDisk($media->disk, $media->getPathRelativeToRoot())
                ->as($this->filename($membership, $file, $media))
                ->withMime($media->mime_type);
        }

        return $result;
    }

    /**
     * @return Collection<int, MemberFile>
     */
    private function orderedFiles(UserMembership $membership)
    {
        return $membership->files()->with('media')->get()->sortBy(function (MemberFile $file) {
            $position = array_search($file->type, self::PRIORITY, true);

            return $position === false ? count(self::PRIORITY) : $position;
        })->values();
    }

    private function label(MemberFile $file): string
    {
        return ucfirst(str_replace('_', ' ', $file->type));
    }

    /**
     * Built from our own data, never the uploader's filename.
     */
    private function filename(UserMembership $membership, MemberFile $file, Media $media): string
    {
        $reference = preg_replace('/[^A-Za-z0-9._-]+/', '-', (string) ($membership->membership_number ?? "application-{$membership->id}"));
        $type = preg_replace('/[^a-z0-9_-]+/', '-', (string) $file->type);
        $extension = self::EXTENSIONS[$media->mime_type] ?? (preg_replace('/[^a-z0-9]/', '', strtolower($media->extension)) ?: 'bin');

        return "{$reference}-{$type}.{$extension}";
    }
}
