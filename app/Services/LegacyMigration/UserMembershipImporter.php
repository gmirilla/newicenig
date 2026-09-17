<?php

namespace App\Services\LegacyMigration;

use App\Enums\MembershipStatus;
use App\Legacy\LegacyUserMembership;
use App\Models\UserMembership;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Imports the old app's `i_c_e_n_user_memberships`, reconstructing each
 * applicant's submitted EAV form data via {@see FieldMapper}. Depends on
 * {@see UserImporter} and {@see MembershipTierImporter} having already run.
 */
class UserMembershipImporter
{
    protected FieldMapper $fieldMapper;

    /** @var array<int, int> Legacy user_membership id => new user_membership id. */
    protected array $idMap = [];

    /**
     * Legacy user id => new user_membership id of that user's EARLIEST legacy
     * membership. A file attached directly to a legacy user (not a specific
     * membership — see FileImporter) belongs on their first application,
     * since documents like certificates are submitted once, not per renewal.
     *
     * @var array<int, int>
     */
    protected array $earliestMembershipIdByUser = [];

    /**
     * Membership numbers already claimed by an earlier-processed legacy row
     * in this run. Real data has old memberships sharing the same
     * `i_c_e_n_number` (a genuine data-quality issue, not a mapping bug) —
     * since `membership_number` is unique on the new schema, only the first
     * (lowest legacy id) claimant keeps it; later duplicates are cleared and
     * flagged rather than crashing the import.
     *
     * @var array<string, true>
     */
    protected array $usedMembershipNumbers = [];

    public function __construct(
        protected ImportReport $report,
        protected bool $dryRun,
        protected UserImporter $users,
        protected MembershipTierImporter $tiers,
    ) {
        $this->fieldMapper = new FieldMapper;
    }

    public function run(): void
    {
        LegacyUserMembership::query()->orderBy('id')->chunk(100, function ($legacyMemberships): void {
            foreach ($legacyMemberships as $legacyMembership) {
                $this->importOne($legacyMembership);
            }
        });
    }

    protected function importOne(LegacyUserMembership $legacy): void
    {
        $newUserId = $this->users->newUserIdFor((int) $legacy->user_id);
        $newTierId = $this->tiers->newTierIdFor((int) $legacy->i_c_e_n_membership_id);

        if (! $this->dryRun) {
            if (! $newUserId) {
                $this->report->skipped('memberships', "Legacy membership #{$legacy->id}: user #{$legacy->user_id} was not imported.");

                return;
            }

            if (! $newTierId) {
                $this->report->skipped('memberships', "Legacy membership #{$legacy->id}: tier #{$legacy->i_c_e_n_membership_id} was not imported.");

                return;
            }
        }

        $mapped = $this->fieldMapper->map($legacy->formData());

        if (! empty($mapped['extra'])) {
            $this->report->flagged(
                'memberships',
                "Legacy membership #{$legacy->id} (member #{$legacy->i_c_e_n_number}): unmapped fields [".implode(', ', array_keys($mapped['extra']))."] saved to extra_fields."
            );
        }

        $attributes = array_merge($mapped['columns'], [
            'user_id' => $newUserId,
            'membership_tier_id' => $newTierId,
            'membership_number' => $this->resolveMembershipNumber($legacy),
            'status' => $this->mapStatus($legacy->status, $legacy->expired_at),
            'extra_fields' => $mapped['extra'] ?: null,
            'verified_at' => $legacy->verified_at,
            'expires_at' => $legacy->expired_at,
        ]);

        if ($this->dryRun) {
            $this->idMap[$legacy->id] = 0;
            $this->report->imported('memberships');

            return;
        }

        // Keyed on the legacy row's own id, not membership_number or
        // [user_id, tier_id] — both can collide across genuinely distinct
        // legacy applications in real data, which would silently overwrite
        // one with the other instead of importing both.
        $membership = DB::transaction(fn () => UserMembership::updateOrCreate(
            ['legacy_membership_id' => $legacy->id],
            $attributes,
        ));

        $this->idMap[$legacy->id] = $membership->id;

        // Rows are processed in ascending legacy id order, so the first time
        // we see a given user is their earliest membership.
        if (! isset($this->earliestMembershipIdByUser[(int) $legacy->user_id])) {
            $this->earliestMembershipIdByUser[(int) $legacy->user_id] = $membership->id;
        }

        $this->report->imported('memberships');
    }

    protected function resolveMembershipNumber(LegacyUserMembership $legacy): ?string
    {
        $number = trim((string) $legacy->i_c_e_n_number);

        if ($number === '') {
            return null;
        }

        if (isset($this->usedMembershipNumbers[$number])) {
            $this->report->flagged(
                'memberships',
                "Legacy membership #{$legacy->id}: membership number '{$number}' is already used by another legacy membership — cleared here, needs manual review."
            );

            return null;
        }

        $this->usedMembershipNumbers[$number] = true;

        return $number;
    }

    protected function mapStatus(?string $oldStatus, ?CarbonInterface $expiredAt): MembershipStatus
    {
        return match (strtolower((string) $oldStatus)) {
            'approved' => $expiredAt && $expiredAt->isPast() ? MembershipStatus::Expired : MembershipStatus::Active,
            'pending' => MembershipStatus::PendingReview,
            default => MembershipStatus::PendingReview,
        };
    }

    public function newMembershipIdFor(int $legacyId): ?int
    {
        $id = $this->idMap[$legacyId] ?? null;

        return $id ?: null;
    }

    public function earliestMembershipIdForLegacyUser(int $legacyUserId): ?int
    {
        $id = $this->earliestMembershipIdByUser[$legacyUserId] ?? null;

        return $id ?: null;
    }
}
