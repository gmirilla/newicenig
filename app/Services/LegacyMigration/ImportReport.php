<?php

namespace App\Services\LegacyMigration;

/**
 * Collects per-entity counts and messages across a `legacy:migrate` run so the
 * command can print one summary at the end instead of interleaving output.
 */
class ImportReport
{
    /** @var array<string, int> */
    protected array $imported = [];

    /** @var array<string, array<int, string>> */
    protected array $skipped = [];

    /** @var array<string, array<int, string>> */
    protected array $flagged = [];

    public function imported(string $entity): void
    {
        $this->imported[$entity] = ($this->imported[$entity] ?? 0) + 1;
    }

    public function skipped(string $entity, string $reason): void
    {
        $this->skipped[$entity][] = $reason;
    }

    public function flagged(string $entity, string $reason): void
    {
        $this->flagged[$entity][] = $reason;
    }

    /**
     * @return array<int, array{entity: string, imported: int, skipped: int, flagged: int}>
     */
    public function summaryRows(): array
    {
        $entities = collect($this->imported)
            ->keys()
            ->merge(array_keys($this->skipped))
            ->merge(array_keys($this->flagged))
            ->unique()
            ->values();

        return $entities->map(fn (string $entity) => [
            'entity' => $entity,
            'imported' => $this->imported[$entity] ?? 0,
            'skipped' => count($this->skipped[$entity] ?? []),
            'flagged' => count($this->flagged[$entity] ?? []),
        ])->all();
    }

    /**
     * @return array<int, string>
     */
    public function skippedReasons(string $entity): array
    {
        return $this->skipped[$entity] ?? [];
    }

    /**
     * @return array<int, string>
     */
    public function flaggedReasons(string $entity): array
    {
        return $this->flagged[$entity] ?? [];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function allFlagged(): array
    {
        return $this->flagged;
    }
}
