<?php

namespace App\Services\LegacyMigration;

use App\Legacy\LegacyMembershipTier;
use App\Models\MembershipTier;

/**
 * Imports the old app's `i_c_e_n_memberships` (membership tiers). New
 * tier-behavior flags that don't exist in the old schema (`requires_*`,
 * `benefits`) are defaulted sensibly; an admin can fine-tune them afterward.
 */
class MembershipTierImporter
{
    /** @var array<int, int> Legacy tier id => new tier id. */
    protected array $idMap = [];

    public function __construct(protected ImportReport $report, protected bool $dryRun) {}

    public function run(): void
    {
        foreach (LegacyMembershipTier::query()->orderBy('id')->get() as $legacyTier) {
            $this->importOne($legacyTier);
        }
    }

    protected function importOne(LegacyMembershipTier $legacyTier): void
    {
        $name = trim((string) $legacyTier->name);

        if ($name === '') {
            $this->report->skipped('tiers', "Legacy tier #{$legacyTier->id} has no name.");

            return;
        }

        $attributes = [
            'abbreviation' => $legacyTier->abbreviation,
            'description' => $legacyTier->description,
            'registration_fee' => $legacyTier->fee ?? 0,
            'renewal_fee' => $legacyTier->renewal_fee ?? 0,
            'currency' => 'NGN',
            'is_active' => (bool) $legacyTier->is_active,
        ];

        if ($this->dryRun) {
            $existing = MembershipTier::where('name', $name)->first();
            $this->idMap[$legacyTier->id] = $existing?->id ?? 0;
            $this->report->imported('tiers');

            return;
        }

        $tier = MembershipTier::updateOrCreate(['name' => $name], $attributes);

        $this->idMap[$legacyTier->id] = $tier->id;
        $this->report->imported('tiers');
    }

    public function newTierIdFor(int $legacyTierId): ?int
    {
        $id = $this->idMap[$legacyTierId] ?? null;

        return $id ?: null;
    }
}
