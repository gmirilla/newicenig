<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            CmsSeeder::class,
            MembershipSeeder::class,
        ]);

        // Known-password staff accounts are for local development only — never seed
        // predictable credentials into a production database.
        if (app()->isProduction()) {
            return;
        }

        $this->seedStaffAccount('Super Admin', 'super-admin@icen.test', 'super-admin');
        $this->seedStaffAccount('Admin', 'admin@icen.test', 'admin');
        $this->seedStaffAccount('Registrar', 'registrar@icen.test', 'registrar');
    }

    protected function seedStaffAccount(string $name, string $email, string $role): void
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $user->syncRoles([$role]);
    }
}
