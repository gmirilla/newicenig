<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Protect the roles that gate admin panel access (see User::PANEL_ROLES) from
        // being renamed or deleted through any code path, not just the Filament UI —
        // canAccessPanel() checks these by name, so either action would silently lock
        // out everyone holding that role.
        Role::saving(function (Role $role) {
            $original = $role->getOriginal('name');

            if ($original && in_array($original, User::PANEL_ROLES, true) && $role->name !== $original) {
                throw new \RuntimeException("The \"{$original}\" role cannot be renamed because it controls admin panel access.");
            }
        });

        Role::deleting(function (Role $role) {
            if (in_array($role->name, User::PANEL_ROLES, true)) {
                throw new \RuntimeException("The \"{$role->name}\" role cannot be deleted because it controls admin panel access.");
            }
        });
    }
}
