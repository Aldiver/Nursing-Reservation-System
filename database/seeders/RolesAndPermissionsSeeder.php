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
        $noter = Role::create(['name' => 'Noter']);
        $approver = Role::create(['name' => 'Approver']);

        // Create Permissions
        $show = Permission::create(['name' => 'show']);
        $create = Permission::create(['name' => 'create']);
        $edit = Permission::create(['name' => 'edit']);
        $delete = Permission::create(['name' => 'delete']);
        $note = Permission::create(['name' => 'noter']);
        $approve = Permission::create(['name' => 'approver']);

        // Assign Permissions to Roles
        $admin->givePermissionTo($create, $edit, $delete, $show, $note, $approve);
        $staff->givePermissionTo($show, $create, $edit, $delete);
        $noter->givePermissionTo($show, $create, $edit, $delete, $note);
        $approver->givePermissionTo($show, $create, $edit, $delete, $approve);

        // Create a super admin user
        $user = \App\Models\User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($admin);
    }
}
