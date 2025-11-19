<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        Permission::create([
            'name' => 'manage-users',
            'display_name_en' => 'manage-users',
            'display_name_ps' => 'manage-users',
            'display_name_dr' => 'manage-users',
        ]);
        Permission::create([
            'name' => 'view-users',
            'display_name_en' => 'view-users',
            'display_name_ps' => 'view-users',
            'display_name_dr' => 'view-users',
        ]);

        $admin = Role::create([
            'name' => 'admin',
            'display_name_en' => 'admin',
            'display_name_ps' => 'admin',
            'display_name_dr' => 'admin',
        ]);

        $manager = Role::create([
            'name' => 'manager',
            'display_name_en' => 'manager',
            'display_name_ps' => 'manager',
            'display_name_dr' => 'manager',
        ]);

        $admin->givePermissionTo(['manage-users', 'view-users']);
        $manager->givePermissionTo(['view-users']);

        $user = User::create([
            'first_name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'job_title' => 'Admin',
        ]);
        $user->assignRole('admin');

        $managerUser = User::create([
            'first_name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'job_title' => 'Manager',
        ]);
        $managerUser->assignRole('manager');
    }
}
