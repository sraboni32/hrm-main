<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionRoleSeeder extends Seeder
{
    public function run()
    {
        // Ensure the 'task_view' permission exists
        $taskViewPermission = Permission::firstOrCreate([
            'name' => 'task_view',
            'guard_name' => 'web',
        ]);

        // Get Super Admin role
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            // Attach 'task_view' permission if not already assigned
            if (!$superAdmin->hasPermissionTo('task_view')) {
                $superAdmin->givePermissionTo('task_view');
            }
        }

        // ... existing code for other permissions/roles ...
    }
} 