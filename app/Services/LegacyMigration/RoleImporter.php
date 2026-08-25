<?php

namespace App\Services\LegacyMigration;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Old app's flat `admin` role (via `spatie/laravel-permission`, same package
 * the new app uses) maps onto the new app's `admin` role — the org promotes
 * specific people to `super-admin`/`registrar` manually afterward.
 */
class RoleImporter
{
    public function __construct(
        protected ImportReport $report,
        protected bool $dryRun,
        protected UserImporter $users,
    ) {}

    public function run(): void
    {
        $legacyAdminUserIds = DB::connection('legacy')->table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'admin')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('model_has_roles.model_id');

        foreach ($legacyAdminUserIds as $legacyUserId) {
            $newUserId = $this->users->newUserIdFor((int) $legacyUserId);

            if (! $newUserId) {
                $this->report->skipped('roles', "Legacy admin user #{$legacyUserId} was not imported.");

                continue;
            }

            if ($this->dryRun) {
                $this->report->imported('roles');

                continue;
            }

            User::find($newUserId)?->assignRole('admin');
            $this->report->imported('roles');
        }
    }
}
