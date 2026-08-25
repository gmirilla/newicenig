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
            'membership_number' => $legacy->i_c_e_n_number,
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

        $key = $legacy->i_c_e_n_number
            ? ['membership_number' => $legacy->i_c_e_n_number]
            : ['user_id' => $newUserId, 'membership_tier_id' => $newTierId];

        $membership = DB::transaction(fn () => UserMembership::updateOrCreate($key, $attributes));

        $this->idMap[$legacy->id] = $membership->id;
        $this->report->imported('memberships');
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
}
