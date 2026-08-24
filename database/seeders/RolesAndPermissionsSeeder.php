<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Seed the application's roles.
     */
    public function run(): void
    {
        collect(['super-admin', 'admin', 'registrar'])
            ->each(fn (string $role) => Role::findOrCreate($role));
    }
}
