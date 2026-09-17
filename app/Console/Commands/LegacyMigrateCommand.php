<?php

namespace App\Console\Commands;

use App\Services\LegacyMigration\FileImporter;
use App\Services\LegacyMigration\ImportReport;
use App\Services\LegacyMigration\MembershipTierImporter;
use App\Services\LegacyMigration\PaymentImporter;
use App\Services\LegacyMigration\RoleImporter;
use App\Services\LegacyMigration\UserImporter;
use App\Services\LegacyMigration\UserMembershipImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Imports real member data from the old icenig.org.ng app's database (restored
 * locally to a separate `legacy` connection — see .env.example) into this app.
 *
 * Always run without `--force` first: it's a dry run that reports what would
 * happen without writing anything. Only pass `--force` once that report looks
 * right. See the approved migration plan for full context and field mapping.
 */
#[Signature('legacy:migrate {--force : Actually write changes; without this the command only reports what it would do} {--only= : Comma-separated subset of: users,tiers,roles,memberships,payments,files}')]
#[Description('Import members, memberships, payments, and files from the old app')]
class LegacyMigrateCommand extends Command
{
    protected const STEPS = ['users', 'tiers', 'roles', 'memberships', 'payments', 'files'];

    public function handle(): int
    {
        $dryRun = ! $this->option('force');
        $only = $this->resolveSteps();

        if ($only === null) {
            $this->components->error('--only must be a comma-separated subset of: '.implode(', ', self::STEPS));

            return self::FAILURE;
        }

        // This is a one-shot, largely irreversible data import. --force already
        // means "actually write" (not "skip confirmation"), so in production we
        // still require an explicit interactive yes even when --force is passed.
        if (! $dryRun && $this->laravel->environment('production')) {
            $this->components->warn('You are about to run legacy:migrate --force against a PRODUCTION environment.');

            if (! $this->confirm('This will write real data and cannot be safely undone. Continue?', false)) {
                $this->components->error('Aborted.');

                return self::FAILURE;
            }
        }

        $this->components->info($dryRun
            ? 'Dry run — nothing will be written. Pass --force to actually import.'
            : 'Importing for real — writing to the database.');

        $report = new ImportReport;

        $users = new UserImporter($report, $dryRun);
        $tiers = new MembershipTierImporter($report, $dryRun);
        $roles = new RoleImporter($report, $dryRun, $users);
        $memberships = new UserMembershipImporter($report, $dryRun, $users, $tiers);
        $payments = new PaymentImporter($report, $dryRun, $users, $memberships);
        $files = new FileImporter($report, $dryRun, $memberships);

        // Dependency order: a step needs every earlier step's id map already populated.
        if (in_array('users', $only, true)) {
            $this->step('Users', fn () => $users->run());
        }

        if (in_array('tiers', $only, true)) {
            $this->step('Membership tiers', fn () => $tiers->run());
        }

        if (in_array('roles', $only, true)) {
            $this->step('Roles', fn () => $roles->run());
        }

        if (in_array('memberships', $only, true)) {
            $this->step('User memberships', fn () => $memberships->run());
        }

        if (in_array('payments', $only, true)) {
            $this->step('Payments', fn () => $payments->run());
        }

        if (in_array('files', $only, true)) {
            $this->step('Files', fn () => $files->run());
        }

        $this->printSummary($report);

        return self::SUCCESS;
    }

    protected function step(string $label, \Closure $callback): void
    {
        $this->components->task($label, function () use ($callback) {
            $callback();

            return true;
        });
    }

    /**
     * @return array<int, string>|null
     */
    protected function resolveSteps(): ?array
    {
        $only = $this->option('only');

        if (! $only) {
            return self::STEPS;
        }

        $requested = array_map('trim', explode(',', (string) $only));

        if (array_diff($requested, self::STEPS) !== []) {
            return null;
        }

        // Preserve dependency order regardless of how the user listed them.
        return array_values(array_intersect(self::STEPS, $requested));
    }

    protected function printSummary(ImportReport $report): void
    {
        $this->newLine();
        $this->components->info('Summary');

        $this->table(
            ['Entity', 'Imported', 'Skipped', 'Flagged for review'],
            $report->summaryRows(),
        );

        foreach ($report->allSkipped() as $entity => $reasons) {
            $this->components->warn("Skipped — {$entity}:");
            $this->printReasons($reasons);
        }

        foreach ($report->allFlagged() as $entity => $reasons) {
            $this->components->warn("Flagged for manual review — {$entity}:");
            $this->printReasons($reasons);
        }
    }

    /**
     * @param  array<int, string>  $reasons
     */
    protected function printReasons(array $reasons, int $limit = 20): void
    {
        foreach (array_slice($reasons, 0, $limit) as $reason) {
            $this->line("  - {$reason}");
        }

        $remaining = count($reasons) - $limit;

        if ($remaining > 0) {
            $this->line("  ... and {$remaining} more (redirect output to a file to see all, e.g. `> migrate.log`).");
        }
    }
}
