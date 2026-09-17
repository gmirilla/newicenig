<?php

namespace App\Services\LegacyMigration;

use App\Legacy\LegacyFile;
use App\Models\MemberFile;
use App\Models\User;

/**
 * Imports the old app's `files` table, copying each physical upload from the
 * old app's `storage/app/public` directory ({@see config('legacy.storage_path')})
 * into the new app's Media Library. The old schema has no explicit file
 * category, so the {@see MemberFile} `type` is inferred from the file's name/
 * description/path; anything ambiguous defaults to `other`.
 *
 * `fileable_type` in real data is either a membership application (attach
 * directly) or a user (verified against production data — every genuine
 * member document, e.g. certificates, is attached to the user, not a specific
 * membership) — attached to that user's EARLIEST membership, since documents
 * like certificates are submitted once at registration, not per renewal.
 */
class FileImporter
{
    public function __construct(
        protected ImportReport $report,
        protected bool $dryRun,
        protected UserMembershipImporter $memberships,
    ) {}

    public function run(): void
    {
        $storagePath = config('legacy.storage_path');

        if (! $this->dryRun && ! $storagePath) {
            $this->report->skipped('files', 'LEGACY_STORAGE_PATH is not configured — no files were copied.');

            return;
        }

        LegacyFile::query()->orderBy('id')->chunk(100, function ($legacyFiles) use ($storagePath): void {
            foreach ($legacyFiles as $legacyFile) {
                $this->importOne($legacyFile, $storagePath);
            }
        });
    }

    protected function importOne(LegacyFile $legacy, ?string $storagePath): void
    {
        $fileableType = (string) $legacy->fileable_type;

        // false is a distinct sentinel from null: it means "we don't know how
        // to handle this fileable type at all" (flagged), vs. a resolvable
        // type whose target just isn't available in this run (skipped).
        $newMembershipId = match (true) {
            str_contains($fileableType, 'Membership') => $this->memberships->newMembershipIdFor((int) $legacy->fileable_id),
            $fileableType === User::class => $this->memberships->earliestMembershipIdForLegacyUser((int) $legacy->fileable_id),
            default => false,
        };

        if ($newMembershipId === false) {
            $this->report->flagged(
                'files',
                "Legacy file #{$legacy->id} ({$legacy->name}): fileable type [{$fileableType}] is not a member document — needs manual review."
            );

            return;
        }

        if (! $this->dryRun && ! $newMembershipId) {
            $this->report->skipped('files', "Legacy file #{$legacy->id}: no imported membership found for fileable #{$legacy->fileable_id} ({$fileableType}).");

            return;
        }

        $type = $this->inferType((string) $legacy->name, (string) $legacy->description, (string) $legacy->path);

        if ($this->dryRun) {
            $this->report->imported('files');

            return;
        }

        // Re-running the import must not duplicate files or media attachments —
        // each legacy file is only ever copied in once.
        if (MemberFile::where('legacy_file_id', $legacy->id)->exists()) {
            $this->report->skipped('files', "Legacy file #{$legacy->id} was already imported.");

            return;
        }

        $absolutePath = rtrim((string) $storagePath, '/\\').DIRECTORY_SEPARATOR
            .ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, (string) $legacy->path), '/\\');

        if (! is_file($absolutePath)) {
            $this->report->skipped('files', "Legacy file #{$legacy->id}: source file not found at {$absolutePath}.");

            return;
        }

        $memberFile = MemberFile::create([
            'user_membership_id' => $newMembershipId,
            'type' => $type,
            'legacy_file_id' => $legacy->id,
        ]);

        $memberFile->addMedia($absolutePath)
            ->preservingOriginal()
            ->usingFileName(basename($absolutePath))
            ->toMediaCollection('file');

        $this->report->imported('files');
    }

    protected function inferType(string $name, string $description, string $path): string
    {
        $haystack = strtolower("{$name} {$description} {$path}");

        return match (true) {
            str_contains($haystack, 'passport') => 'passport_photo',
            str_contains($haystack, 'primary') => 'primary_school_certificate',
            str_contains($haystack, 'secondary') => 'secondary_school_certificate',
            str_contains($haystack, 'higher'), str_contains($haystack, 'tertiary'), str_contains($haystack, 'degree') => 'higher_institution_certificate',
            default => 'other',
        };
    }
}
