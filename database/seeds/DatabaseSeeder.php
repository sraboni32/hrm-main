<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            \Database\Seeders\PermissionsSeeder::class,
            \Database\Seeders\RoleSeeder::class,
            \Database\Seeders\PermissionRoleSeeder::class,
        ]);
    }
}
