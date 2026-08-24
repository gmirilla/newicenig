<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

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

        $admin = User::factory()->create([
            'name' => 'ICEN Admin',
            'email' => 'admin@icen.test',
        ]);

        $admin->assignRole('super-admin');
    }
}
