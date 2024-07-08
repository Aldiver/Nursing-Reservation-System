<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Roles
        $admin = Role::create(['name' => 'Admin']);
        $staff = Role::create(['name' => 'Staff']);

        // Create Permissions
        $list = Permission::create(['name' => 'list']);
        $create = Permission::create(['name' => 'create']);
        $edit = Permission::create(['name' => 'edit']);
        $delete = Permission::create(['name' => 'delete']);
        $noter = Permission::create(['name' => 'noter']);
        $approver = Permission::create(['name' => 'approver']);

        // Assign Permissions to Roles
        $admin->givePermissionTo($edit, $delete, $list, $create, $noter, $approver);

        // Create a super admin user
        $user = \App\Models\User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($admin);
        $user->givePermissionTo($edit, $delete, $list, $create, $noter, $approver);
    }
}
