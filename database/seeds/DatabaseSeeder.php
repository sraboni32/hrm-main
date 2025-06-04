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
            \Database\Seeders\RoleSeeder::class,
            \Database\Seeders\PermissionRoleSeeder::class,
            CurrencySeeder::class,
            SettingSeeder::class,
            UserSeeder::class,
        ]);
    }
}
