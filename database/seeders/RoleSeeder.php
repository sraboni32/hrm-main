<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['name' => 'Super Admin', 'description' => 'Super Admin', 'guard_name' => 'web'],
            ['name' => 'Employee', 'description' => 'Employee Access', 'guard_name' => 'web'],
            ['name' => 'Client', 'description' => 'Client Access', 'guard_name' => 'web'],
        ];
        foreach ($roles as $roleData) {
            Role::firstOrCreate($roleData);
        }
    }
} 